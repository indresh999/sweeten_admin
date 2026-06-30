
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ isset($banner) ? 'Edit Banner' : 'Add Banner' }}</h4>
    </div>
    <div class="card shadow-sm" style="max-width:700px"><div class="card-body">
        <form method="POST" action="{{ isset($banner) ? route('admin.banners.update',$banner->id) : route('admin.banners.store') }}" enctype="multipart/form-data">
            @csrf @if(isset($banner)) @method('PUT') @endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Banner Image * @if(isset($banner))(Leave empty to keep existing)@endif</label>
                    <input type="file" name="image" class="form-control" accept="image/*" {{ !isset($banner)?'required':'' }}>
                    @if(isset($banner))<img src="{{ $banner->image_url }}" class="mt-2 rounded" height="80" style="object-fit:cover">@endif
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title',$banner->title??'') }}" placeholder="Optional banner title">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Banner Type *</label>
                    <select name="banner_type" class="form-select" required>
                        @foreach(['hero','strip','popup','deals','category'] as $type)
                        <option value="{{ $type }}" {{ old('banner_type',$banner->banner_type??'hero')==$type?'selected':'' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Target Type</label>
                    <select name="target_type" class="form-select">
                        @foreach(['none','url','shop','category','item'] as $t)
                        <option value="{{ $t }}" {{ old('target_type',$banner->target_type??'none')==$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Target ID</label>
                    <input type="number" name="target_id" class="form-control" value="{{ old('target_id',$banner->target_id??'') }}" placeholder="Shop/Category/Item ID">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Target URL</label>
                    <input type="url" name="target_url" class="form-control" value="{{ old('target_url',$banner->target_url??'') }}" placeholder="https://...">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', isset($banner->start_date) ? \Carbon\Carbon::parse($banner->start_date)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', isset($banner->end_date) ? \Carbon\Carbon::parse($banner->end_date)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order',$banner->sort_order??0) }}" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status',$banner->status??'active')=='active'?'selected':'' }}>Active</option>
                        <option value="inactive" {{ old('status',$banner->status??'')=='inactive'?'selected':'' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end pb-1">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_sponsored" value="1" {{ old('is_sponsored',$banner->is_sponsored??false)?'checked':'' }}>
                        <label class="form-check-label fw-semibold">Sponsored</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">{{ isset($banner) ? 'Update' : 'Create' }} Banner</button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div></div>
</div>
</x-app-layout>
