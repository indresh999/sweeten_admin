
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ isset($coupon) ? 'Edit Coupon' : 'New Coupon' }}</h4>
    </div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="card shadow-sm" style="max-width:700px">
        <div class="card-body">
        <form method="POST" action="{{ isset($coupon) ? route('admin.coupons.update',$coupon->id) : route('admin.coupons.store') }}">
            @csrf @if(isset($coupon)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Coupon Code *</label>
                    <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code',$coupon->code??'') }}" {{ isset($coupon)?'readonly':'' }} required placeholder="e.g. SWEET50">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title',$coupon->title??'') }}" required placeholder="e.g. Flat 50 off on first order">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Short description for app">{{ old('description',$coupon->description??'') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Discount Type *</label>
                    <select name="discount_type" class="form-select" id="discountType" required>
                        <option value="percent" {{ old('discount_type',$coupon->discount_type??'')=='percent'?'selected':'' }}>Percentage (%)</option>
                        <option value="flat" {{ old('discount_type',$coupon->discount_type??'')=='flat'?'selected':'' }}>Flat Amount (₹)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Discount Value *</label>
                    <input type="number" name="discount_value" class="form-control" value="{{ old('discount_value',$coupon->discount_value??'') }}" min="1" step="0.01" required>
                </div>
                <div class="col-md-4" id="maxDiscountWrap">
                    <label class="form-label fw-semibold">Max Discount (₹)</label>
                    <input type="number" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount',$coupon->max_discount_amount??'') }}" min="0" placeholder="Leave blank = no cap">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Min Order Amount (₹) *</label>
                    <input type="number" name="min_order_amount" class="form-control" value="{{ old('min_order_amount',$coupon->min_order_amount??0) }}" min="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Total Usage Limit</label>
                    <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit',$coupon->usage_limit??'') }}" min="1" placeholder="Unlimited">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Per User Limit *</label>
                    <input type="number" name="usage_per_user" class="form-control" value="{{ old('usage_per_user',$coupon->usage_per_user??1) }}" min="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Valid From *</label>
                    <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from', isset($coupon) ? \Carbon\Carbon::parse($coupon->valid_from)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Valid Until *</label>
                    <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', isset($coupon) ? \Carbon\Carbon::parse($coupon->valid_until)->format('Y-m-d') : now()->addMonth()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $coupon->is_active??true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="isActive">Active (visible in app)</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">{{ isset($coupon) ? 'Update Coupon' : 'Create Coupon' }}</button>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>
<script>
document.getElementById('discountType').addEventListener('change', function(){
    document.getElementById('maxDiscountWrap').style.display = this.value==='percent' ? '' : 'none';
});
document.getElementById('discountType').dispatchEvent(new Event('change'));
</script>
</x-app-layout>
