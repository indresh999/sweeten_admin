<div class="cat-card {{ $rowClass }}"
     draggable="true"
     data-id="{{ $cat->id }}"
     data-name="{{ $cat->category_name }}"
     data-img="{{ $cat->image ? asset($cat->image) : '' }}">

    <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>

    @if($cat->image)
        <img src="{{ asset($cat->image) }}" class="cat-img" alt="{{ $cat->category_name }}">
    @else
        <div class="cat-img-placeholder"><i class="fas fa-image"></i></div>
    @endif

    <div class="flex-grow-1 min-w-0">
        <div class="fw-semibold text-truncate" style="font-size:13px">{{ $cat->category_name }}</div>
        @if($cat->is_featured)
            <span class="badge bg-warning text-dark" style="font-size:9px">⭐ Featured</span>
        @endif
    </div>
</div>
