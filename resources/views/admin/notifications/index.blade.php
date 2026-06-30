
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Push Notifications</h4>
        <div>
            <span class="badge bg-primary-subtle text-primary me-2">{{ $sentCount }} Sent</span>
            <a href="{{ route('admin.notifications.history') }}" class="btn btn-sm btn-outline-secondary">History</a>
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Send Push Notification</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.notifications.send') }}">
                        @csrf
                        @if($errors->any())<div class="alert alert-danger small"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Send To *</label>
                            <select name="target" class="form-select" id="targetSelect" required>
                                <option value="all_customers">All Customers</option>
                                <option value="all_delivery">All Delivery Boys</option>
                                <option value="all_vendors">All Vendors (Email)</option>
                                <option value="specific_customer">Specific Customer</option>
                                <option value="specific_vendor">Specific Vendor (Email)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="targetIdWrap" style="display:none">
                            <label class="form-label fw-semibold">User/Vendor ID</label>
                            <input type="number" name="target_id" class="form-control" placeholder="Enter ID">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Weekend Sale! 🎉" maxlength="200">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Message *</label>
                            <textarea name="body" class="form-control" rows="4" required placeholder="Your notification message..." maxlength="1000"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Image URL (optional)</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Quick Stats</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Total Notifications Sent</span><strong>{{ $sentCount }}</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Unread</span><strong>{{ $unreadCount }}</strong></div>
                </div>
            </div>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Firebase Setup</h6></div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Make sure your FCM Server Key is configured in Settings → Firebase.</p>
                    <a href="{{ route('admin.settings.index') }}#firebase" class="btn btn-sm btn-outline-warning">⚙ Configure Firebase</a>
                </div>
            </div>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Tips</h6></div>
                <div class="card-body">
                    <ul class="small text-muted ps-3 mb-0">
                        <li>All Customers — sends to all non-blocked users with FCM token</li>
                        <li>All Delivery — sends to all active riders</li>
                        <li>All Vendors — sends email (no app for vendors)</li>
                        <li>Max 500 tokens per batch (auto-batched)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('targetSelect').addEventListener('change', function(){
    const specific = ['specific_customer','specific_vendor'].includes(this.value);
    document.getElementById('targetIdWrap').style.display = specific ? '' : 'none';
});
</script>
</x-app-layout>
