<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AppSetting;

class AdminSplashController extends Controller
{
    public function index()
    {
        $splash = [
            'media_type'    => AppSetting::get('splash_media_type', 'none'),
            'media_path'    => AppSetting::get('splash_media_path'),
            'duration'      => AppSetting::get('splash_duration', 3),
            'skip_enabled'  => AppSetting::get('splash_skip_enabled', '1'),
            'title'         => AppSetting::get('splash_title', ''),
            'subtitle'      => AppSetting::get('splash_subtitle', ''),
            'overlay_opacity' => AppSetting::get('splash_overlay_opacity', '40'),
            'title_color'   => AppSetting::get('splash_title_color', '#ffffff'),
            'status'        => AppSetting::get('splash_status', '0'),
        ];

        return view('admin.splash.index', compact('splash'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'duration'        => 'required|integer|min:1|max:30',
            'skip_enabled'    => 'nullable|boolean',
            'title'           => 'nullable|string|max:120',
            'subtitle'        => 'nullable|string|max:255',
            'overlay_opacity' => 'required|integer|min:0|max:100',
            'title_color'     => 'required|string|max:20',
            'status'          => 'nullable|boolean',
        ]);

        AppSetting::set('splash_duration',        $request->duration);
        AppSetting::set('splash_skip_enabled',    $request->skip_enabled ? '1' : '0');
        AppSetting::set('splash_title',           $request->title ?? '');
        AppSetting::set('splash_subtitle',        $request->subtitle ?? '');
        AppSetting::set('splash_overlay_opacity', $request->overlay_opacity);
        AppSetting::set('splash_title_color',     $request->title_color);
        AppSetting::set('splash_status',          $request->status ? '1' : '0');

        return back()->with('success', 'Splash screen settings saved.');
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,png,jpg,webp,gif,mp4,mov,webm|max:51200',
        ]);

        $file      = $request->file('media');
        $mime      = $file->getMimeType();
        $mediaType = str_starts_with($mime, 'video') ? 'video' : 'image';

        // Delete old file
        $old = AppSetting::get('splash_media_path');
        if ($old) {
            Storage::disk('public')->delete($old);
        }

        $path = $file->store('splash', 'public');

        AppSetting::set('splash_media_path', $path);
        AppSetting::set('splash_media_type', $mediaType);

        return back()->with('success', ucfirst($mediaType) . ' uploaded successfully.');
    }

    public function removeMedia()
    {
        $path = AppSetting::get('splash_media_path');
        if ($path) {
            Storage::disk('public')->delete($path);
        }

        AppSetting::set('splash_media_path', null);
        AppSetting::set('splash_media_type', 'none');

        return back()->with('success', 'Splash media removed.');
    }

    // Stream video/image with Range support so browsers can play video via php artisan serve
    public function streamMedia(Request $request)
    {
        $path     = AppSetting::get('splash_media_path');
        $fullPath = Storage::disk('public')->path($path);

        if (!$path || !file_exists($fullPath)) {
            abort(404);
        }

        $size     = filesize($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $start    = 0;
        $end      = $size - 1;

        $headers = [
            'Content-Type'   => $mimeType,
            'Accept-Ranges'  => 'bytes',
            'Content-Length' => $size,
        ];

        // Handle Range request
        if ($request->hasHeader('Range')) {
            preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches);
            $start = (int) $matches[1];
            $end   = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $size - 1;

            $headers['Content-Range']  = "bytes {$start}-{$end}/{$size}";
            $headers['Content-Length'] = $end - $start + 1;

            return response()->stream(function () use ($fullPath, $start, $end) {
                $fp = fopen($fullPath, 'rb');
                fseek($fp, $start);
                $remaining = $end - $start + 1;
                while ($remaining > 0 && !feof($fp)) {
                    $chunk = min(8192, $remaining);
                    echo fread($fp, $chunk);
                    $remaining -= $chunk;
                }
                fclose($fp);
            }, 206, $headers);
        }

        return response()->stream(function () use ($fullPath) {
            $fp = fopen($fullPath, 'rb');
            while (!feof($fp)) echo fread($fp, 8192);
            fclose($fp);
        }, 200, $headers);
    }
}
