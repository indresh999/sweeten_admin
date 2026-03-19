<x-app-layout :assets="$assets ?? []">

@section('title', 'Add Platform Fee')

@section('content')

<div class="card">
    <div class="card-header">
        <h3>Add Platform Fee</h3>
    </div>

    <form action="{{ route('platform-fee.store') }}" method="POST">
        @csrf

        <div class="card-body">

            @include('admin.platform_fee.form')

        </div>

        <div class="card-footer">
            <button class="btn btn-success">Save</button>
        </div>

    </form>
</div>

@endsection
</x-app-layout>