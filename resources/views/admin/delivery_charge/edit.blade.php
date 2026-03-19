<x-app-layout :assets="$assets ?? []">

@section('content')

<h4>Edit Delivery Charge</h4>

<form action="{{ route('delivery-charge.update',$charge->id) }}" method="POST">
@csrf

@include('admin.delivery_charge.form')

<button type="submit" class="btn btn-success">Update</button>

</form>

</x-app-layout>