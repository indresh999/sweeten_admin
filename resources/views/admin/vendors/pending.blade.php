
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Vendors Pending Approval</h4>
    </div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Store</th><th>Owner</th><th>Email</th><th>Phone</th><th>Registered</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($vendors as $v)
            <tr>
                <td>{{ $v->shop_id }}</td>
                <td><strong>{{ $v->restaurant_name }}</strong><br><small class="text-muted">{{ $v->city }}</small></td>
                <td>{{ $v->full_name }}</td>
                <td>{{ $v->email }}</td>
                <td>{{ $v->phone_number ?? '—' }}</td>
                <td class="small text-muted">{{ $v->created_at->format('d M Y') }}</td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('admin.vendors.show', $v->shop_id) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                        <form method="POST" action="{{ route('admin.vendors.approve', $v->shop_id) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success py-0 px-2">✓ Approve</button></form>
                        <button class="btn btn-sm btn-danger py-0 px-2" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $v->shop_id }}">✗ Reject</button>
                    </div>
                    {{-- Reject Modal --}}
                    <div class="modal fade" id="rejectModal{{ $v->shop_id }}" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Reject Vendor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <form method="POST" action="{{ route('admin.vendors.reject', $v->shop_id) }}">@csrf
                            <div class="modal-body">
                                <label class="form-label">Reason (will be emailed)</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="e.g. Documents incomplete..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-danger btn-sm">Confirm Reject</button>
                            </div>
                            </form>
                        </div></div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No pending vendors.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-3 pb-2">{{ $vendors->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
