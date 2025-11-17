<x-app-layout :assets="$assets ?? []">

    <div class="container py-4">
        <h3>Document Verification</h3>

        <div class="card mt-3 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <strong>Document Details</strong>
            </div>

            <div class="card-body">

                <p><strong>Delivery Boy:</strong> {{ $document->deliveryBoy->full_name }}</p>
                <p><strong>Phone:</strong> {{ $document->deliveryBoy->phone }}</p>

                <p><strong>Document Type:</strong> {{ ucfirst($document->document_type) }}</p>

                <p><strong>Document:</strong></p>
                <img src="{{ asset('uploads/delivery_docs/'.$document->file) }}"
                     class="img-fluid mb-3" style="max-width: 400px;">

                <form method="POST" action="{{ route('admin.delivery.document.verify', $document->id) }}">
                    @csrf

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select" required>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control"></textarea>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-success px-4">Submit</button>
                    </div>
                </form>

            </div>
        </div>

        <a href="{{ route('admin.delivery.pending') }}" class="btn btn-secondary mt-3">← Back</a>

    </div>

</x-app-layout>