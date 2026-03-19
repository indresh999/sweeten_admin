<x-app-layout :assets="$assets ?? []">

@section('title', 'Platform Fees')

@section('content')

<div class="card">
    <div class="card-header">
        <h3>Platform Fees</h3>
        <a href="{{ route('platform-fee.create') }}" class="btn btn-primary float-right">
            Add New
        </a>
    </div>

    <div class="card-body table-responsive">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Handling Fee</th>
                    <th>Packing Fee</th>
                    <th>Order Range</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th width="150">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($fees as $f)
                <tr>
                    <td>{{ $f->id }}</td>
                    <td>₹ {{ $f->handling_fee }}</td>
                    <td>₹ {{ $f->packing_fee }}</td>
                    <td>
                        {{ $f->min_order_amount ?? '0' }}
                        -
                        {{ $f->max_order_amount ?? '∞' }}
                    </td>
                    <td>{{ $f->priority }}</td>
                    <td>
                        <span class="badge badge-{{ $f->status ? 'success' : 'danger' }}">
                            {{ $f->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('platform-fee.edit',$f->id) }}" class="btn btn-warning btn-sm">Edit</a>

                        <a href="{{ route('platform-fee.delete',$f->id) }}"
                           onclick="return confirm('Delete this record?')"
                           class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>

</x-app-layout>