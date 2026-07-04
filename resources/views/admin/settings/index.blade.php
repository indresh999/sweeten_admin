
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <h4 class="fw-bold mb-4">Settings</h4>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Tab Nav --}}
    <ul class="nav nav-tabs mb-3" id="settingsTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general">General</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#firebase" id="firebaseTab">Firebase / FCM</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#smtp">SMTP / Email</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#appinfo">App Info</a></li>
    </ul>

    <div class="tab-content">
        {{-- General --}}
        <div class="tab-pane fade show active" id="general">
            <div class="card shadow-sm" style="max-width:600px"><div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">@csrf
                <div class="mb-3"><label class="form-label fw-semibold">App Name</label><input type="text" name="app_name" class="form-control" value="{{ $settings['app_name'] ?? 'Sweetan' }}"></div>
                <div class="mb-3"><label class="form-label fw-semibold">Support Email</label><input type="email" name="support_email" class="form-control" value="{{ $settings['support_email'] ?? '' }}"></div>
                <div class="mb-3"><label class="form-label fw-semibold">Support Phone</label><input type="text" name="support_phone" class="form-control" value="{{ $settings['support_phone'] ?? '' }}"></div>
                <div class="mb-3"><label class="form-label fw-semibold">Max Delivery Radius (km)</label><input type="number" name="max_delivery_radius_km" class="form-control" value="{{ $settings['max_delivery_radius_km'] ?? 10 }}"></div>
                <div class="mb-3"><label class="form-label fw-semibold">Delivery Boy Max Radius (km)</label><input type="number" name="delivery_max_boy_radius_km" class="form-control" value="{{ $settings['delivery_max_boy_radius_km'] ?? 15 }}"></div>
                <button class="btn btn-primary">Save General Settings</button>
                </form>
            </div></div>
        </div>

        {{-- Firebase --}}
        <div class="tab-pane fade" id="firebase">
            <div class="card shadow-sm" style="max-width:600px"><div class="card-body">
                <div class="alert alert-info small mb-3">
                    <strong>Setup:</strong> Go to <a href="https://console.firebase.google.com" target="_blank">Firebase Console</a> → Project Settings → Cloud Messaging → Server Key (Legacy API)
                </div>
                <form method="POST" action="{{ route('admin.settings.firebase') }}">@csrf
                <div class="mb-3"><label class="form-label fw-semibold">FCM Server Key *</label><input type="text" name="fcm_server_key" class="form-control font-monospace" value="{{ $settings['fcm_server_key'] ?? '' }}" placeholder="AAAA..."></div>
                <div class="mb-3"><label class="form-label fw-semibold">Firebase Project ID</label><input type="text" name="firebase_project_id" class="form-control" value="{{ $settings['firebase_project_id'] ?? '' }}" placeholder="sweetan-app"></div>
                <button class="btn btn-primary">Save Firebase Settings</button>
                </form>

                <hr>
                <h6 class="fw-bold">Flutter Integration Guide</h6>
                <div class="bg-light rounded p-3 font-monospace small">
<pre># pubspec.yaml
firebase_core: ^3.0.0
firebase_messaging: ^15.0.0

# FlutterFire CLI
flutterfire configure

# main.dart — get FCM token
final token = await FirebaseMessaging.instance.getToken();
# Send token to: POST /api/auth/update-profile {fcm_token: token}</pre>
                </div>
            </div></div>
        </div>

        {{-- SMTP --}}
        <div class="tab-pane fade" id="smtp">
            <div class="card shadow-sm" style="max-width:600px"><div class="card-body">
                <div class="alert alert-warning small mb-3">Saving SMTP settings will also update your <code>.env</code> file and clear config cache.</div>
                <form method="POST" action="{{ route('admin.settings.smtp') }}">@csrf
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label fw-semibold">Mail Host *</label><input type="text" name="mail_host" class="form-control" value="{{ $settings['mail_host'] ?? 'smtp.gmail.com' }}" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Port *</label><input type="number" name="mail_port" class="form-control" value="{{ $settings['mail_port'] ?? 587 }}" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Username *</label><input type="email" name="mail_username" class="form-control" value="{{ $settings['mail_username'] ?? '' }}" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Password *</label><input type="password" name="mail_password" class="form-control" placeholder="Leave blank to keep current"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">From Address *</label><input type="email" name="mail_from_address" class="form-control" value="{{ $settings['mail_from_address'] ?? '' }}" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">From Name *</label><input type="text" name="mail_from_name" class="form-control" value="{{ $settings['mail_from_name'] ?? 'Sweetan' }}" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Encryption</label>
                        <select name="mail_encryption" class="form-select">
                            <option value="tls" {{ ($settings['mail_encryption']??'tls')=='tls'?'selected':'' }}>TLS</option>
                            <option value="ssl" {{ ($settings['mail_encryption']??'')=='ssl'?'selected':'' }}>SSL</option>
                        </select>
                    </div>
                    <div class="col-12"><button class="btn btn-primary">Save SMTP Settings</button></div>
                </div>
                </form>
            </div></div>
        </div>

        {{-- App Info --}}
        <div class="tab-pane fade" id="appinfo">
            <div class="card shadow-sm" style="max-width:700px"><div class="card-body">
                <div class="alert alert-info small mb-3">These settings are displayed in the mobile app and exposed via the public API endpoint <code>GET /api/app-settings</code>.</div>
                <form method="POST" action="{{ route('admin.settings.appinfo') }}">@csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">App Version</label>
                        <input type="text" name="app_version" class="form-control" value="{{ $settings['app_version'] ?? '1.0.0' }}" placeholder="e.g. 1.2.0">
                        <small class="text-muted">Used by the app to check for updates</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Help Email</label>
                        <input type="email" name="help_email" class="form-control" value="{{ $settings['help_email'] ?? '' }}" placeholder="support@sweetan.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Help Phone</label>
                        <input type="text" name="help_phone" class="form-control" value="{{ $settings['help_phone'] ?? '' }}" placeholder="+91 98765 43210">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">About Us</label>
                        <textarea name="about_us" class="form-control" rows="4" placeholder="About your app/company...">{{ $settings['about_us'] ?? '' }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Privacy Policy</label>
                        <textarea name="privacy_policy" class="form-control" rows="4" placeholder="Privacy policy content...">{{ $settings['privacy_policy'] ?? '' }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Refund Policy</label>
                        <textarea name="refund_policy" class="form-control" rows="4" placeholder="Refund policy content...">{{ $settings['refund_policy'] ?? '' }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Cancellation Policy</label>
                        <textarea name="cancellation_policy" class="form-control" rows="4" placeholder="Cancellation policy content...">{{ $settings['cancellation_policy'] ?? '' }}</textarea>
                    </div>
                    <div class="col-12"><button class="btn btn-primary">Save App Info</button></div>
                </div>
                </form>
            </div></div>
        </div>
    </div>
</div>
<script>
// Auto-open firebase tab if #firebase in URL
if(window.location.hash === '#firebase') {
    document.getElementById('firebaseTab').click();
}
</script>
</x-app-layout>
