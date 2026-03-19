<x-app-layout :assets="$assets ?? []">


@section('content')
    <a href="{{ route('delivery-charge.create') }}" class="btn btn-primary mb-3">Add Delivery Charge</a>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Min Distance</th>
            <th>Max Distance</th>
            <th>Charge</th>
            <th>Free Above</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        @foreach ($charges as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->min_distance }}</td>
                <td>{{ $row->max_distance }}</td>
                <td>{{ $row->charge_amount }}</td>
                <td>{{ $row->free_above_amount }}</td>
                <td>{{ $row->priority }}</td>
                <td>
                    @if ($row->status == 1)
                        Active
                    @else
                        Inactive
                    @endif
                </td>

                <td>

                    <a href="{{ route('delivery-charge.edit', $row->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <a href="{{ route('delivery-charge.delete', $row->id) }}" class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete?')">Delete</a>

                </td>

            </tr>
        @endforeach

    </table>
</x-app-layout>
