
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Notification History</h4>
    </div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Title</th><th>Message</th><th>User</th><th>Type</th><th>Read</th><th>Sent At</th></tr></thead>
            <tbody>
            @forelse($notifications as $n)
            <tr>
                <td class="fw-semibold">{{ $n->title }}</td>
                <td class="text-muted small">{{ Str::limit($n->body,60) }}</td>
                <td>{{ $n->user?->full_name ?? '—' }}</td>
                <td><span class="badge bg-light text-dark">{{ $n->type }}</span></td>
                <td>@if($n->is_read)<span class="badge bg-success">Read</span>@else<span class="badge bg-secondary">Unread</span>@endif</td>
                <td class="small text-muted">{{ $n->sent_at ? \Carbon\Carbon::parse($n->sent_at)->format('d M Y, h:i A') : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">No notifications yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $notifications->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
