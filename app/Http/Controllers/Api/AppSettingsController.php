<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class AppSettingsController extends Controller
{
    public function index()
    {
        $keys = [
            'app_version',
            'app_name',
            'help_email',
            'help_phone',
            'about_us',
            'privacy_policy',
            'refund_policy',
            'cancellation_policy',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = AppSetting::get($key, '');
        }

        return response()->json(['data' => $settings]);
    }

    public function splashMedia(\Illuminate\Http\Request $request)
    {
        $path     = AppSetting::get('splash_media_path');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);

        if (!$path || !file_exists($fullPath)) abort(404);

        $size     = filesize($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $start    = 0;
        $end      = $size - 1;
        $headers  = ['Content-Type' => $mimeType, 'Accept-Ranges' => 'bytes', 'Content-Length' => $size];

        if ($request->hasHeader('Range')) {
            preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $m);
            $start = (int) $m[1];
            $end   = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : $size - 1;
            $headers['Content-Range']  = "bytes {$start}-{$end}/{$size}";
            $headers['Content-Length'] = $end - $start + 1;

            return response()->stream(function () use ($fullPath, $start, $end) {
                $fp = fopen($fullPath, 'rb'); fseek($fp, $start);
                $rem = $end - $start + 1;
                while ($rem > 0 && !feof($fp)) { $c = min(8192, $rem); echo fread($fp, $c); $rem -= $c; }
                fclose($fp);
            }, 206, $headers);
        }

        return response()->stream(function () use ($fullPath) {
            $fp = fopen($fullPath, 'rb');
            while (!feof($fp)) echo fread($fp, 8192);
            fclose($fp);
        }, 200, $headers);
    }

    public function splashConfig()
    {
        $path = AppSetting::get('splash_media_path');

        return response()->json([
            'status'          => AppSetting::get('splash_status', '0') === '1',
            'media_type'      => AppSetting::get('splash_media_type', 'none'),
            'media_url'       => $path ? url('/api/splash-media') : null,
            'duration'        => (int) AppSetting::get('splash_duration', 3),
            'skip_enabled'    => AppSetting::get('splash_skip_enabled', '1') !== '0',
            'title'           => AppSetting::get('splash_title', ''),
            'subtitle'        => AppSetting::get('splash_subtitle', ''),
            'overlay_opacity' => (int) AppSetting::get('splash_overlay_opacity', 40),
            'title_color'     => AppSetting::get('splash_title_color', '#ffffff'),
        ]);
    }
}
