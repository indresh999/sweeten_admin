<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <h3>Delivery Boy Profile</h3>

    <div class="card shadow-sm mt-3">
        <div class="card-body">

            <p><strong>Name:</strong> {{ $boy->full_name }}</p>
            <p><strong>Phone:</strong> {{ $boy->phone }}</p>
            <p><strong>Email:</strong> {{ $boy->email }}</p>

            <p><strong>Status:</strong>
                <span class="badge bg-{{ $boy->status == 'blocked' ? 'danger' : 'success' }}">
                    {{ ucfirst($boy->status) }}
                </span>
            </p>

            <p><strong>Verification:</strong>
                <span class="badge bg-{{ $boy->is_verified ? 'success' : 'danger' }}">
                    {{ $boy->is_verified ? 'Verified' : 'Not Verified' }}
                </span>
            </p>

            <hr>

            <h5>Documents</h5>
            @foreach($boy->documents as $doc)
                <div class="mb-3">
                    <strong>{{ ucfirst($doc->document_type) }}</strong><br>
                    Status:
                    <span class="badge bg-{{ $doc->status == 'approved' ? 'success' : ($doc->status == 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($doc->status) }}
                    </span>
                    <br>
                    <img src="{{ asset('uploads/delivery_docs/'.$doc->file) }}" width="200" class="mt-2">
                </div>
            @endforeach

        </div>
    </div>

    <a href="{{ route('admin.delivery.boys') }}" class="btn btn-secondary mt-3">← Back to List</a>

</div>

</x-app-layout>