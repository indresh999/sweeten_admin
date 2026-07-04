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
}
