<x-app-layout :assets="$assets ?? []">
<div class="container py-4">
    <h3>Item #{{ $item->id }} — {{ $item->item_name }}</h3>

    <div class="row">
        <div class="col-md-4">
            @php $images = $item->images ? json_decode($item->images,true) : []; @endphp
            @if($images)
                @foreach($images as $img)
                    <img src="{{ asset('uploads/items/'.$img) }}" class="img-fluid mb-2">
                @endforeach
            @else
                <div class="border text-center p-4">No images</div>
            @endif
        </div>

        <div class="col-md-8">
            <p><strong>Category:</strong> {{ $item->category->category_name ?? '-' }}</p>
            <p><strong>Owner:</strong> {{ $item->owner->restaurant_name ?? '-' }}</p>
            <p><strong>Price:</strong> ₹{{ number_format($item->price,2) }}</p>
            <p><strong>Offer Price:</strong> {{ $item->offer_price ? '₹'.number_format($item->offer_price,2) : '-' }}</p>
            <p><strong>GST:</strong> {{ $item->gst_percent ?? '-' }}%</p>
            <p><strong>Min Qty:</strong> {{ $item->min_quantity ?? '-' }}</p>
            <p><strong>Weight/Piece:</strong> {{ $item->weight_or_piece ?? '-' }}</p>
            <p><strong>Description:</strong><br>{!! nl2br(e($item->description)) !!}</p>
            <p><strong>Status:</strong>
                <span class="badge bg-{{ $item->status === 'active' ? 'success' : 'danger' }}">
                    {{ ucfirst($item->status) }}
                </span>
            </p>
            <a href="{{ route('admin.items.edit', $item->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
</x-app-layout>