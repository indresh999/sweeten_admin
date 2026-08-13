<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-5">
    {{-- Breadcrumb --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.policies.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Policies
        </a>
        <div>
            <h4 class="fw-bold mb-0">{{ isset($policy) ? 'Edit Policy' : 'New Policy' }}</h4>
            <p class="text-muted mb-0 small">{{ isset($policy) ? 'Update policy content & settings' : 'Create a new policy page for the app' }}</p>
        </div>
    </div>

    <form method="POST"
          action="{{ isset($policy) ? route('admin.policies.update', $policy->id) : route('admin.policies.store') }}"
          id="policyForm">
        @csrf
        @if(isset($policy)) @method('PUT') @endif

        @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4">
            <strong>Please fix the errors below:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="row g-4">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">

                {{-- Content --}}
                <div class="section-card" style="background:#fff;border:1px solid #e9ecef;border-radius:14px;padding:20px 22px;margin-bottom:20px;">
                    <div style="font-size:13px;font-weight:700;color:#495057;text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f3f5;">
                        ✏️ Policy Content
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title', $policy->title ?? '') }}"
                               placeholder="e.g. Privacy Policy" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug <span class="text-muted fw-normal">(auto-generated if empty)</span></label>
                        <input type="text" name="slug" class="form-control"
                               value="{{ old('slug', $policy->slug ?? '') }}"
                               placeholder="e.g. privacy-policy">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Content</label>
                        <textarea name="content" class="form-control" id="contentEditor" rows="20">{{ old('content', $policy->content ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-4">

                {{-- Settings --}}
                <div class="section-card" style="background:#fff;border:1px solid #e9ecef;border-radius:14px;padding:20px 22px;margin-bottom:20px;">
                    <div style="font-size:13px;font-weight:700;color:#495057;text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f3f5;">
                        ⚙️ Settings
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ old('is_active', $policy->is_active ?? true) ? 'selected' : '' }}>🟢 Active (shown in app)</option>
                            <option value="0" {{ old('is_active', $policy->is_active ?? true) ? '' : 'selected' }}>⚫ Inactive (hidden)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort Order <span class="text-muted fw-normal">(lower = first)</span></label>
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', $policy->sort_order ?? 0) }}" min="0" max="999">
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">
                        <i class="fas fa-{{ isset($policy) ? 'save' : 'plus' }} me-2"></i>
                        {{ isset($policy) ? 'Update Policy' : 'Create Policy' }}
                    </button>
                    <a href="{{ route('admin.policies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#contentEditor',
    height: 500,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | bold italic forecolor | ' +
             'alignleft aligncenter alignright alignjustify | ' +
             'bullist numlist outdent indent | removeformat | help',
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; padding: 16px; }',
    branding: false,
    promotion: false,
    menubar: 'file edit view insert format tools table help',
});
</script>
@endpush
</x-app-layout>
