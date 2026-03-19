<x-app-layout :assets="$assets ?? []">
@section('title', 'Edit Platform Fee')

@section('content')

<div class="card">
    <div class="card-header">
        <h3>Edit Platform Fee</h3>
    </div>

    <form action="{{ route('platform-fee.update', $fee->id) }}" method="POST">
        @csrf

        <div class="card-body">

            @include('admin.platform_fee.form')

        </div>

        <div class="card-footer">
            <button class="btn btn-success">Update</button>
        </div>

    </form>
</div>

</x-app-layout>