<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AppUser;
use App\Models\AppOwnerUser;
use App\Models\DeliveryBoy;
use App\Models\Notification;
use App\Models\AppSetting;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $sentCount    = Notification::count();
        $unreadCount  = Notification::where('is_read',0)->count();
        return view('admin.notifications.index', compact('sentCount','unreadCount'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:200',
            'body'      => 'required|string|max:1000',
            'target'    => 'required|in:all_customers,all_vendors,all_delivery,specific_customer,specific_vendor,topic',
            'target_id' => 'nullable|integer',
            'image_url' => 'nullable|url|max:500',
        ]);

        $tokens = [];
        $userIds = [];

        switch ($request->target) {
            case 'all_customers':
                $users   = AppUser::where('is_blocked',0)->whereNotNull('fcm_token')->get(['id','fcm_token']);
                $tokens  = $users->pluck('fcm_token')->filter()->values()->toArray();
                $userIds = $users->pluck('id')->toArray();
                break;
            case 'all_vendors':
                // Vendors don't have FCM in current schema; send DB notification
                break;
            case 'all_delivery':
                $tokens = DeliveryBoy::where('status','!=','blocked')->whereNotNull('fcm_token')
                    ->pluck('fcm_token')->filter()->values()->toArray();
                break;
            case 'specific_customer':
                $user   = AppUser::find($request->target_id);
                if ($user?->fcm_token) { $tokens[]  = $user->fcm_token; $userIds[] = $user->id; }
                break;
            case 'specific_vendor':
                // No FCM for vendors in current schema
                break;
        }

        $sent = 0;
        $failed = 0;

        if (!empty($tokens)) {
            $result = $this->sendFcm($request->title, $request->body, $tokens, $request->image_url);
            $sent   = $result['success'] ?? 0;
            $failed = $result['failure'] ?? 0;
        }

        // Store in DB notifications for customers
        foreach ($userIds as $uid) {
            Notification::create([
                'user_id'   => $uid,
                'user_type' => 'app_user',
                'title'     => $request->title,
                'body'      => $request->body,
                'type'      => 'broadcast',
                'is_read'   => false,
                'sent_at'   => now(),
            ]);
        }

        $msg = "Notification sent. FCM success: {$sent}, failed: {$failed}, DB stored: ".count($userIds);
        return back()->with('success', $msg);
    }

    public function history(Request $request)
    {
        $notifications = Notification::with('user:id,full_name')
            ->when($request->type, fn($q) => $q->where('type',$request->type))
            ->latest('sent_at')->paginate(30)->withQueryString();
        return view('admin.notifications.history', compact('notifications'));
    }

    private function sendFcm(string $title, string $body, array $tokens, ?string $imageUrl = null): array
    {
        $fcmKey = AppSetting::get('fcm_server_key');
        if (!$fcmKey) {
            Log::warning('[FCM] No server key configured');
            return ['success'=>0,'failure'=>count($tokens)];
        }

        // Batch in chunks of 500 (FCM limit)
        $success = 0; $failure = 0;
        foreach (array_chunk($tokens, 500) as $chunk) {
            try {
                $payload = [
                    'registration_ids' => $chunk,
                    'notification' => array_filter([
                        'title' => $title,
                        'body'  => $body,
                        'image' => $imageUrl,
                        'sound' => 'default',
                        'badge' => '1',
                    ]),
                    'data' => ['type'=>'broadcast','click_action'=>'FLUTTER_NOTIFICATION_CLICK'],
                    'priority' => 'high',
                    'android' => ['priority'=>'high','notification'=>['channel_id'=>'sweetan_orders']],
                    'apns' => ['payload'=>['aps'=>['sound'=>'default','badge'=>1]]],
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key='.$fcmKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', $payload);

                $result   = $response->json();
                $success += $result['success'] ?? 0;
                $failure += $result['failure'] ?? 0;
            } catch (\Exception $e) {
                Log::error('[FCM] Send failed: '.$e->getMessage());
                $failure += count($chunk);
            }
        }

        return ['success'=>$success,'failure'=>$failure];
    }
}
