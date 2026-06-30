<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all()->pluck('value','key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except(['_token','_method']) as $key => $value) {
            AppSetting::set($key, $value);
        }
        return back()->with('success','Settings updated.');
    }

    public function updateFirebase(Request $request)
    {
        $request->validate([
            'fcm_server_key'     => 'required|string',
            'firebase_project_id'=> 'nullable|string',
        ]);
        AppSetting::set('fcm_server_key',      $request->fcm_server_key);
        AppSetting::set('firebase_project_id', $request->firebase_project_id ?? '');
        return back()->with('success','Firebase settings updated.');
    }

    public function updateSmtp(Request $request)
    {
        $request->validate([
            'mail_host'       => 'required|string',
            'mail_port'       => 'required|integer',
            'mail_username'   => 'required|email',
            'mail_password'   => 'required|string',
            'mail_from_address'=>'required|email',
            'mail_from_name'  => 'required|string',
            'mail_encryption' => 'nullable|in:tls,ssl,none',
        ]);
        foreach (['mail_host','mail_port','mail_username','mail_password','mail_from_address','mail_from_name','mail_encryption'] as $key) {
            AppSetting::set($key, $request->input($key, ''));
        }
        // Also write to .env for runtime use
        $this->writeEnv([
            'MAIL_HOST'           => $request->mail_host,
            'MAIL_PORT'           => $request->mail_port,
            'MAIL_USERNAME'       => $request->mail_username,
            'MAIL_PASSWORD'       => $request->mail_password,
            'MAIL_FROM_ADDRESS'   => $request->mail_from_address,
            'MAIL_FROM_NAME'      => '"'.$request->mail_from_name.'"',
            'MAIL_ENCRYPTION'     => $request->mail_encryption ?? 'tls',
        ]);
        return back()->with('success','SMTP settings updated.');
    }

    public function updateGst(Request $request)
    {
        $request->validate(['global_gst_percent'=>'required|numeric|min:0|max:100']);
        AppSetting::set('global_gst_percent', $request->global_gst_percent);
        return back()->with('success','GST rate updated.');
    }

    private function writeEnv(array $data): void
    {
        $path    = base_path('.env');
        $content = file_get_contents($path);
        foreach ($data as $key => $value) {
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }
        file_put_contents($path, $content);
    }
}
