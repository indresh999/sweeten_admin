<x-app-layout :assets="$assets ?? []">

    <div class="container py-4">
        <h3 class="mb-4">Pending Delivery Boy Documents</h3>

        <div class="card">
            <div class="card-body">

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Delivery Boy</th>
                            <th>Phone</th>
                            <th>Document Type</th>
                            <th>Submitted On</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td>{{ $doc->deliveryBoy->full_name }}</td>
                                <td>{{ $doc->deliveryBoy->phone }}</td>
                                <td>{{ ucfirst($doc->document_type) }}</td>
                                <td>{{ $doc->created_at->format('d M Y') }}</td>

                                <td>
                                    <a href="{{ route('admin.delivery.document.show', $doc->id) }}"
                                       class="btn btn-primary btn-sm">
                                        View & Verify
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No pending documents</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

    </div>

</x-app-layout>