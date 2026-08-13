<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Payment Submissions</h3>
        <a href="{{ route('admin.delivery.wallet.index') }}" class="btn btn-secondary">← Back to Wallet</a>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6>Pending</h6>
                    <h2>{{ $stats['pending'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Approved</h6>
                    <h2>{{ $stats['approved'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Rejected</h6>
                    <h2>{{ $stats['rejected'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('admin.delivery.wallet.submissions', array_merge(request()->except('status'), ['status' => 'all'])) }}" 
           class="btn btn-sm {{ request('status', 'pending') == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
        <a href="{{ route('admin.delivery.wallet.submissions', array_merge(request()->except('status'), ['status' => 'pending'])) }}" 
           class="btn btn-sm {{ request('status') == 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">Pending</a>
        <a href="{{ route('admin.delivery.wallet.submissions', array_merge(request()->except('status'), ['status' => 'approved'])) }}" 
           class="btn btn-sm {{ request('status') == 'approved' ? 'btn-success' : 'btn-outline-success' }}">Approved</a>
        <a href="{{ route('admin.delivery.wallet.submissions', array_merge(request()->except('status'), ['status' => 'rejected'])) }}" 
           class="btn btn-sm {{ request('status') == 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejected</a>
    </div>

    <!-- Submissions Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Delivery Boy</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Screenshot</th>
                        <th>Status</th>
                        <th>Admin Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $index => $sub)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $sub->boy->full_name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $sub->boy->email ?? '' }}</small>
                            </td>
                            <td><strong>₹{{ number_format($sub->amount, 0) }}</strong></td>
                            <td>{{ $sub->submission_date }}</td>
                            <td>
                                <a href="{{ asset('storage/'.$sub->screenshot_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    View
                                </a>
                            </td>
                            <td>
                                @if($sub->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($sub->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>{{ $sub->admin_notes ?? '-' }}</td>
                            <td>
                                @if($sub->status == 'pending')
                                    <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $sub->id }}">
                                        Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $sub->id }}">
                                        Reject
                                    </button>
                                @else
                                    <span class="text-muted">{{ ucfirst($sub->status) }} at {{ $sub->verified_at ? $sub->verified_at->format('M d, H:i') : '-' }}</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Approve Modal -->
                        @if($sub->status == 'pending')
                        <div class="modal fade" id="approveModal{{ $sub->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.delivery.wallet.verify', $sub->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="approved">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">Approve Payment</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Approve <strong>₹{{ number_format($sub->amount, 0) }}</strong> from {{ $sub->boy->full_name ?? 'N/A' }}?</p>
                                            <div class="mb-3">
                                                <label class="form-label">Notes (optional)</label>
                                                <textarea name="notes" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Approve</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $sub->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.delivery.wallet.verify', $sub->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="rejected">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Reject Payment</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Reject <strong>₹{{ number_format($sub->amount, 0) }}</strong> from {{ $sub->boy->full_name ?? 'N/A' }}?</p>
                                            <div class="mb-3">
                                                <label class="form-label">Reason *</label>
                                                <textarea name="notes" class="form-control" rows="2" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr><td colspan="8">No submissions found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $submissions->links() }}
        </div>
    </div>

</div>

</x-app-layout>
