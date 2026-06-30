
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <h4 class="fw-bold mb-4">Tax & Billing Settings</h4>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-3">
        {{-- Platform Settings --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Platform Fee Settings</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tax.update') }}">@csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Global GST % <small class="text-muted">(applied to all items without category-specific GST)</small></label>
                        <div class="input-group"><input type="number" name="global_gst_percent" class="form-control" step="0.01" min="0" max="100" value="{{ $settings['global_gst_percent'] }}"><span class="input-group-text">%</span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Delivery Base Charge (₹)</label>
                        <input type="number" name="delivery_base_charge" class="form-control" step="0.01" min="0" value="{{ $settings['delivery_base_charge'] }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Free Delivery Above (₹)</label>
                        <input type="number" name="free_delivery_above" class="form-control" step="0.01" min="0" value="{{ $settings['free_delivery_above'] }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Minimum Order Amount (₹)</label>
                        <input type="number" name="min_order_amount" class="form-control" step="0.01" min="0" value="{{ $settings['min_order_amount'] }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Default Delivery Time (minutes)</label>
                        <input type="number" name="default_delivery_minutes" class="form-control" min="1" value="{{ $settings['default_delivery_minutes'] }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Delivery Boy Earning per Order (₹)</label>
                        <input type="number" name="delivery_earn_per_order" class="form-control" step="0.01" min="0" value="{{ $settings['delivery_earn_per_order'] }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Platform Commission % (default)</label>
                        <div class="input-group"><input type="number" name="platform_commission_pct" class="form-control" step="0.01" min="0" max="100" value="{{ $settings['platform_commission_pct'] }}"><span class="input-group-text">%</span></div>
                    </div>
                    <button class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Category Commission --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Category-wise GST & Commission</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>Category</th><th>Commission</th><th>Type</th><th></th></tr></thead>
                        <tbody>
                        @foreach($categories as $cat)
                        <tr>
                            <td>{{ $cat->category_name }}</td>
                            <td>{{ $cat->commission_percent ?? '—' }}{{ $cat->commission_percent ? ($cat->commission_type==='percent'?'%':'₹') : '' }}</td>
                            <td><span class="badge bg-light text-dark">{{ ucfirst($cat->commission_type ?? 'Not set') }}</span></td>
                            <td><a href="/admin/category-commission/{{ $cat->id }}" class="btn btn-sm btn-outline-primary py-0">Edit</a></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
