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

            <h5>Wallet Management</h5>
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title text-muted">Wallet Limit</h6>
                            <h3 class="text-primary">₹{{ number_format($boy->wallet_limit ?? 0, 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title text-muted">Collected Cash</h6>
                            <h3 class="text-warning">₹{{ number_format($boy->wallet_collected ?? 0, 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title text-muted">Pending Submission</h6>
                            <h3 class="text-{{ ($boy->has_pending_submission ?? false) ? 'danger' : 'success' }}">
                                {{ ($boy->has_pending_submission ?? false) ? 'Yes' : 'No' }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.delivery.wallet.limit', $boy->id) }}" method="POST" class="mb-3">
                @csrf
                @method('PUT')
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" name="wallet_limit" class="form-control" 
                           value="{{ $boy->wallet_limit ?? 0 }}" min="0" step="100">
                    <button type="submit" class="btn btn-primary">Set Wallet Limit</button>
                </div>
            </form>

            <a href="{{ route('admin.delivery.wallet.submissions', ['delivery_boy_id' => $boy->id]) }}" 
               class="btn btn-outline-success btn-sm mb-3">
                View Payment Submissions
            </a>

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