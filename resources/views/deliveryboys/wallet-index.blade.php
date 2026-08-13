<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Delivery Wallet Management</h3>
        <a href="{{ route('admin.delivery.wallet.submissions') }}" class="btn btn-warning">Payment Submissions</a>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Total Boys</h6>
                    <h2>{{ $stats['total_boys'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>With Wallet Limit</h6>
                    <h2>{{ $stats['boys_with_limit'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6>Pending Submissions</h6>
                    <h2>{{ $stats['pending_submissions'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>Total Collected</h6>
                    <h2>₹{{ number_format($stats['total_collected'], 0) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" class="mb-3">
        <div class="input-group" style="max-width: 400px;">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </div>
    </form>

    <!-- Boys Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Wallet Limit</th>
                        <th>Collected</th>
                        <th>Remaining</th>
                        <th>Pending</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($boys as $index => $boy)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $boy->full_name }}</td>
                            <td>{{ $boy->email }}</td>
                            <td>
                                <strong>₹{{ number_format($boy->wallet_limit, 0) }}</strong>
                            </td>
                            <td>
                                <span class="text-warning fw-bold">₹{{ number_format($boy->wallet_collected, 0) }}</span>
                            </td>
                            <td>
                                ₹{{ number_format(max(0, $boy->wallet_limit - $boy->wallet_collected), 0) }}
                            </td>
                            <td>
                                @if($boy->has_pending_submission)
                                    <span class="badge bg-danger">Pending</span>
                                @else
                                    <span class="badge bg-success">Clear</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#walletModal{{ $boy->id }}">
                                    Set Limit
                                </button>
                                <a href="{{ route('admin.delivery.wallet.submissions', ['boy_id' => $boy->id]) }}" 
                                   class="btn btn-sm btn-outline-success">Submissions</a>
                            </td>
                        </tr>

                        <!-- Wallet Limit Modal -->
                        <div class="modal fade" id="walletModal{{ $boy->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.delivery.wallet.limit', $boy->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Set Wallet Limit — {{ $boy->full_name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted">Current limit: ₹{{ number_format($boy->wallet_limit, 0) }}</p>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" name="wallet_limit" class="form-control" 
                                                       value="{{ $boy->wallet_limit }}" min="0" step="100" required>
                                            </div>
                                            <small class="text-muted">Maximum cash this delivery boy can collect before submitting.</small>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Limit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="8">No delivery boys found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $boys->links() }}
        </div>
    </div>

</div>

</x-app-layout>
