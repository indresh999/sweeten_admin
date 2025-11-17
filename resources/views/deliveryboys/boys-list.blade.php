<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <h3 class="mb-4">Delivery Boy Management</h3>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Verification</th>
                        <th>Document Status</th>
                        <th>Working Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($boys as $index => $boy)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $boy->full_name }}</td>
                            <td>{{ $boy->phone }}</td>

                            <td>
                                <span class="badge bg-{{ $boy->is_verified ? 'success' : 'danger' }}">
                                    {{ $boy->is_verified ? 'Verified' : 'Not Verified' }}
                                </span>
                            </td>

                            <td>
                                @php $doc = $boy->documents->last(); @endphp
                                @if (!$doc)
                                    <span class="badge bg-secondary">No Document</span>
                                @else
                                    <span class="badge bg-{{ 
                                        $doc->status == 'approved' ? 'success' :
                                        ($doc->status == 'pending' ? 'warning' : 'danger')
                                    }}">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-{{ $boy->status == 'blocked' ? 'danger' : 'success' }}">
                                    {{ ucfirst($boy->status) }}
                                </span>
                            </td>

                            <td>

                                <a href="{{ route('admin.delivery.profile', $boy->id) }}"
                                   class="btn btn-primary btn-sm mb-1">Profile</a>

                                <a href="{{ route('admin.delivery.orders', $boy->id) }}"
                                   class="btn btn-success btn-sm mb-1">Orders</a>

                                @if($doc)
                                    <a href="{{ route('admin.delivery.document.show', $doc->id) }}"
                                       class="btn btn-info btn-sm mb-1">Document</a>
                                @endif

                                <a href="{{ route('admin.delivery.toggle', $boy->id) }}"
                                   class="btn btn-warning btn-sm mb-1">
                                    {{ $boy->status == 'blocked' ? 'Unblock' : 'Block' }}
                                </a>


                                <a href="{{ route('admin.delivery.earnings', $boy->id) }}"
                                    class="btn btn-dark btn-sm mb-1">
                                        Earnings
                                    </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No delivery boys found.</td></tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>

</x-app-layout>