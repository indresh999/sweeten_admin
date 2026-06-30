
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Pending Documents</h4>
    </div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Boy</th><th>Document Type</th><th>Uploaded</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($docs as $doc)
            <tr>
                <td>{{ $doc->id }}</td>
                <td>
                    <a href="{{ route('admin.delivery.show',$doc->delivery_boy_id) }}" class="fw-semibold text-primary">{{ $doc->deliveryBoy?->full_name }}</a><br>
                    <small class="text-muted">{{ $doc->deliveryBoy?->phone_number }}</small>
                </td>
                <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_',' ',$doc->doc_type)) }}</span></td>
                <td class="small text-muted">{{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}</td>
                <td>
                    <div class="d-flex gap-2 align-items-center">
                        @if($doc->file_path)<a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0">View File</a>@endif
                        <form method="POST" action="{{ route('admin.delivery.docs.approve',$doc->id) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-success py-0">✓ Approve</button>
                        </form>
                        <button class="btn btn-sm btn-danger py-0" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $doc->id }}">✗ Reject</button>
                    </div>
                    <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Reject Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <form method="POST" action="{{ route('admin.delivery.docs.reject',$doc->id) }}">@csrf
                        <div class="modal-body"><label class="form-label">Reason</label><textarea name="remarks" class="form-control" rows="2" required></textarea></div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger btn-sm">Reject</button></div>
                        </form>
                    </div></div></div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">No pending documents.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $docs->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
