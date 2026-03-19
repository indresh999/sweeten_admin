<x-app-layout :assets="$assets ?? []">

@section('content')
    <h4>Add Delivery Charge</h4>

    <form action="{{ route('delivery-charge.store') }}" method="POST">
        @csrf

        @include('admin.delivery_charge.form')

        <button type="submit" class="btn btn-success">Save</button>

    </form>
</x-app-layout>
