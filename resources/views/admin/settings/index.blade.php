<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Settings</h4>
            <p class="text-muted mb-0 small">Manage your platform configuration</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- Settings Nav (Sidebar on desktop, Horizontal scroll on mobile) --}}
        <div class="col-12 col-lg-3">
            {{-- Mobile: Horizontal scrollable pills --}}
            <div class="d-lg-none mb-4">
                <div class="settings-nav-scroll">
                    <ul class="nav nav-pills settings-nav-pills" id="settingsTabs">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general"><i class="fas fa-cog me-1"></i>General</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#maps"><i class="fas fa-map me-1"></i>Maps</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#firebase"><i class="fas fa-fire me-1"></i>Firebase</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#smtp"><i class="fas fa-envelope me-1"></i>SMTP</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#payment-qr"><i class="fas fa-qrcode me-1"></i>QR Code</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#appinfo"><i class="fas fa-mobile me-1"></i>App Info</a></li>
                    </ul>
                </div>
            </div>

            {{-- Desktop: Vertical sidebar nav --}}
            <div class="card border-0 shadow-sm d-none d-lg-block sticky-top" style="top: 80px;">
                <div class="card-body p-2">
                    <ul class="nav flex-column settings-nav-vertical" id="settingsTabsDesktop">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#general">
                                <i class="fas fa-cog"></i><span>General</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#maps">
                                <i class="fas fa-map-marker-alt"></i><span>Google Maps</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#firebase">
                                <i class="fas fa-fire"></i><span>Firebase / FCM</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#smtp">
                                <i class="fas fa-envelope"></i><span>SMTP / Email</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#payment-qr">
                                <i class="fas fa-qrcode"></i><span>Payment QR</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#appinfo">
                                <i class="fas fa-mobile-alt"></i><span>App Info</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Settings Content --}}
        <div class="col-12 col-lg-9">
            <div class="tab-content">

                {{-- ========== GENERAL ========== --}}
                <div class="tab-pane fade show active" id="general">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h6 class="fw-bold mb-0"><i class="fas fa-cog me-2 text-primary"></i>General Settings</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.settings.update') }}">@csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">App Name</label>
                                        <input type="text" name="app_name" class="form-control" value="{{ $settings['app_name'] ?? 'Sweetan' }}">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">Support Email</label>
                                        <input type="email" name="support_email" class="form-control" value="{{ $settings['support_email'] ?? '' }}" placeholder="support@example.com">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">Support Phone</label>
                                        <input type="text" name="support_phone" class="form-control" value="{{ $settings['support_phone'] ?? '' }}" placeholder="+91 98765 43210">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">Max Delivery Radius (km)</label>
                                        <input type="number" name="max_delivery_radius_km" class="form-control" value="{{ $settings['max_delivery_radius_km'] ?? 10 }}">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">Rider Max Radius (km)</label>
                                        <input type="number" name="delivery_max_boy_radius_km" class="form-control" value="{{ $settings['delivery_max_boy_radius_km'] ?? 15 }}">
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save General Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ========== GOOGLE MAPS ========== --}}
                <div class="tab-pane fade" id="maps">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h6 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2 text-success"></i>Google Maps</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info small mb-4 d-flex align-items-start gap-2">
                                <i class="fas fa-info-circle mt-1"></i>
                                <div>The API key is stored securely on the server. The Flutter app never receives the key directly - all location calls are proxied through your backend.</div>
                            </div>

                            <form method="POST" action="{{ route('admin.settings.maps') }}">@csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Google Maps API Key *</label>
                                    <div class="input-group">
                                        <input type="password" name="google_maps_api_key" id="mapsApiKeyInput"
                                               class="form-control font-monospace"
                                               value="{{ $settings['google_maps_api_key'] ?? '' }}"
                                               placeholder="AIzaSy..." required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="toggleKey()">
                                            <i class="fa fa-eye" id="mapsEyeIcon"></i>
                                        </button>
                                    </div>
                                    @if(!empty($settings['google_maps_api_key']))
                                        <small class="text-success mt-1 d-block">
                                            <i class="fas fa-check-circle me-1"></i>Key is set ({{ strlen($settings['google_maps_api_key']) }} chars, ends in ...{{ substr($settings['google_maps_api_key'], -6) }})
                                        </small>
                                    @else
                                        <small class="text-danger mt-1 d-block">
                                            <i class="fas fa-exclamation-circle me-1"></i>No key set - location search will not work
                                        </small>
                                    @endif
                                </div>
                                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Maps Key</button>
                            </form>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3">Setup Guide</h6>
                            <div class="alert alert-warning small mb-3">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <strong>Common error - REQUEST_DENIED:</strong> Required APIs are not enabled for your key.
                            </div>
                            <ol class="small text-muted ps-3">
                                <li class="mb-2">Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-decoration-underline">Google Cloud Console</a> and create/copy an API Key</li>
                                <li class="mb-3">Enable both APIs in <a href="https://console.cloud.google.com/apis/library" target="_blank" class="text-decoration-underline">API Library</a>:
                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                        <a href="https://console.cloud.google.com/apis/library/places-backend.googleapis.com" target="_blank" class="btn btn-sm btn-outline-primary">Places API (New)</a>
                                        <a href="https://console.cloud.google.com/apis/library/geocoding-backend.googleapis.com" target="_blank" class="btn btn-sm btn-outline-primary">Geocoding API</a>
                                    </div>
                                </li>
                                <li class="mb-2">For production, restrict the key under <strong>API restrictions</strong></li>
                                <li>Paste the key above, save, then test below</li>
                            </ol>
                            <div class="bg-light rounded p-3 font-monospace small mt-3 overflow-auto">
<pre class="mb-0">GET {{ url('/api/location/autocomplete') }}?input=Mumbai
GET {{ url('/api/location/reverse') }}?lat=19.0760&lng=72.8777</pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========== FIREBASE ========== --}}
                <div class="tab-pane fade" id="firebase">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h6 class="fw-bold mb-0"><i class="fas fa-fire me-2 text-warning"></i>Firebase / FCM</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info small mb-4 d-flex align-items-start gap-2">
                                <i class="fas fa-info-circle mt-1"></i>
                                <div>Go to <a href="https://console.firebase.google.com" target="_blank">Firebase Console</a> -> Project Settings -> Cloud Messaging -> Server Key (Legacy API)</div>
                            </div>

                            <form method="POST" action="{{ route('admin.settings.firebase') }}">@csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">FCM Server Key *</label>
                                        <input type="text" name="fcm_server_key" class="form-control font-monospace" value="{{ $settings['fcm_server_key'] ?? '' }}" placeholder="AAAA...">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Firebase Project ID</label>
                                        <input type="text" name="firebase_project_id" class="form-control" value="{{ $settings['firebase_project_id'] ?? '' }}" placeholder="sweetan-app">
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Firebase Settings</button>
                                    </div>
                                </div>
                            </form>

                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">Flutter Integration</h6>
                            <div class="bg-light rounded p-3 font-monospace small overflow-auto">
<pre class="mb-0"># pubspec.yaml
firebase_core: ^3.0.0
firebase_messaging: ^15.0.0

# main.dart - get FCM token
final token = await FirebaseMessaging.instance.getToken();
# Send to: POST /api/auth/update-profile {fcm_token: token}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========== SMTP ========== --}}
                <div class="tab-pane fade" id="smtp">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h6 class="fw-bold mb-0"><i class="fas fa-envelope me-2 text-info"></i>SMTP / Email</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning small mb-4 d-flex align-items-start gap-2">
                                <i class="fas fa-exclamation-triangle mt-1"></i>
                                <div>Saving SMTP settings will also update your <code>.env</code> file.</div>
                            </div>

                            <form method="POST" action="{{ route('admin.settings.smtp') }}">@csrf
                                <div class="row g-3">
                                    <div class="col-12 col-sm-8">
                                        <label class="form-label fw-semibold">Mail Host *</label>
                                        <input type="text" name="mail_host" class="form-control" value="{{ $settings['mail_host'] ?? 'smtp.gmail.com' }}" required>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label fw-semibold">Port *</label>
                                        <input type="number" name="mail_port" class="form-control" value="{{ $settings['mail_port'] ?? 587 }}" required>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">Username *</label>
                                        <input type="email" name="mail_username" class="form-control" value="{{ $settings['mail_username'] ?? '' }}" required>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">Password *</label>
                                        <input type="password" name="mail_password" class="form-control" placeholder="Leave blank to keep current">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">From Address *</label>
                                        <input type="email" name="mail_from_address" class="form-control" value="{{ $settings['mail_from_address'] ?? '' }}" required>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">From Name *</label>
                                        <input type="text" name="mail_from_name" class="form-control" value="{{ $settings['mail_from_name'] ?? 'Sweetan' }}" required>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label fw-semibold">Encryption</label>
                                        <select name="mail_encryption" class="form-select">
                                            <option value="tls" {{ ($settings['mail_encryption']??'tls')=='tls'?'selected':'' }}>TLS</option>
                                            <option value="ssl" {{ ($settings['mail_encryption']??'')=='ssl'?'selected':'' }}>SSL</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save SMTP Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ========== PAYMENT QR ========== --}}
                <div class="tab-pane fade" id="payment-qr">
                    @php
                        $qrPath      = $settings['payment_qr_path'] ?? null;
                        $qrUpdatedAt = $settings['payment_qr_updated_at'] ?? null;
                    @endphp

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0">
                            <h6 class="fw-bold mb-0"><i class="fas fa-qrcode me-2 text-primary"></i>Current Payment QR</h6>
                        </div>
                        <div class="card-body text-center">
                            @if($qrPath)
                                <div class="qr-display-area">
                                    <img src="{{ asset('storage/' . $qrPath) }}"
                                        class="img-fluid rounded border shadow-sm"
                                        style="max-width:220px;max-height:220px;object-fit:contain"
                                        alt="Payment QR Code">
                                    <div class="mt-3">
                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i>QR Active
                                        </span>
                                    </div>
                                    @if($qrUpdatedAt)
                                    <p class="text-muted small mt-2 mb-0">
                                        <i class="fas fa-clock me-1"></i>Updated: {{ \Carbon\Carbon::parse($qrUpdatedAt)->format('d M Y, h:i A') }}
                                    </p>
                                    @endif
                                </div>
                                <div class="d-flex gap-2 justify-content-center mt-3">
                                    <a href="{{ asset('storage/' . $qrPath) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View Full
                                    </a>
                                    <form method="POST" action="{{ route('admin.settings.payment-qr.remove') }}" class="d-inline" onsubmit="return confirm('Remove QR code?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Remove</button>
                                    </form>
                                </div>
                            @else
                                <div class="py-4 text-muted">
                                    <i class="fas fa-qrcode fa-4x opacity-25 d-block mb-3"></i>
                                    <p class="mb-1 fw-semibold">No QR code uploaded</p>
                                    <small>Upload one below</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h6 class="fw-bold mb-0"><i class="fas fa-upload me-2 text-primary"></i>Upload New QR Code</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info small mb-4 d-flex align-items-start gap-2">
                                <i class="fas fa-info-circle mt-1"></i>
                                <div>Upload your UPI / payment gateway QR code. The mobile app displays this on checkout.</div>
                            </div>

                            <form method="POST" action="{{ route('admin.settings.payment-qr') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">QR Code Image <span class="text-danger">*</span></label>
                                    <input type="file" name="qr_image" id="qrFileInput" class="form-control"
                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                        onchange="previewQr(this)" required>
                                    <small class="text-muted">JPG, PNG, WEBP - max 5 MB</small>
                                </div>

                                <div id="qrPreviewWrap" class="mb-3 d-none text-center">
                                    <p class="small fw-semibold text-muted mb-2">Preview</p>
                                    <img id="qrPreviewImg" class="rounded border shadow-sm"
                                        style="max-width:180px;max-height:180px;object-fit:contain" alt="Preview">
                                </div>

                                <button class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i>{{ $qrPath ? 'Replace QR Code' : 'Upload QR Code' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ========== APP INFO ========== --}}
                <div class="tab-pane fade" id="appinfo">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h6 class="fw-bold mb-0"><i class="fas fa-mobile-alt me-2 text-secondary"></i>App Info</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info small mb-4 d-flex align-items-start gap-2">
                                <i class="fas fa-info-circle mt-1"></i>
                                <div>These settings are displayed in the mobile app and exposed via <code>GET /api/app-settings</code>.</div>
                            </div>

                            <form method="POST" action="{{ route('admin.settings.appinfo') }}">@csrf
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">App Version</label>
                                        <input type="text" name="app_version" class="form-control" value="{{ $settings['app_version'] ?? '1.0.0' }}" placeholder="e.g. 1.2.0">
                                        <small class="text-muted">Used by app to check for updates</small>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">Help Email</label>
                                        <input type="email" name="help_email" class="form-control" value="{{ $settings['help_email'] ?? '' }}" placeholder="support@sweetan.com">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold">Help Phone</label>
                                        <input type="text" name="help_phone" class="form-control" value="{{ $settings['help_phone'] ?? '' }}" placeholder="+91 98765 43210">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">About Us</label>
                                        <textarea name="about_us" class="form-control" rows="3" placeholder="About your app...">{{ $settings['about_us'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Privacy Policy</label>
                                        <textarea name="privacy_policy" class="form-control" rows="3" placeholder="Privacy policy...">{{ $settings['privacy_policy'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Refund Policy</label>
                                        <textarea name="refund_policy" class="form-control" rows="3" placeholder="Refund policy...">{{ $settings['refund_policy'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Cancellation Policy</label>
                                        <textarea name="cancellation_policy" class="form-control" rows="3" placeholder="Cancellation policy...">{{ $settings['cancellation_policy'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save App Info</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('js')
<script>
// Handle hash-based tab activation
const _hash = window.location.hash;
if (_hash) {
    const tabLinks = document.querySelectorAll('#settingsTabs a, #settingsTabsDesktop a');
    tabLinks.forEach(link => {
        if (link.getAttribute('href') === _hash) {
            link.click();
        }
    });
}

// QR Preview
function previewQr(input) {
    if (!input.files[0]) return;
    const wrap = document.getElementById('qrPreviewWrap');
    const img  = document.getElementById('qrPreviewImg');
    img.src = URL.createObjectURL(input.files[0]);
    wrap.classList.remove('d-none');
}

// Toggle Maps API key visibility
function toggleKey() {
    const inp = document.getElementById('mapsApiKeyInput');
    const ico = document.getElementById('mapsEyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'fa fa-eye-slash';
    } else {
        inp.type = 'password';
        ico.className = 'fa fa-eye';
    }
}
</script>
@endpush
</x-app-layout>
