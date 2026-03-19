<x-app-layout :assets="$assets ?? []">

@section('content')
    <h4>Item Commission</h4>

    <form method="POST" action="{{ route('admin.item.commission.update', $item->id) }}">
        @csrf

        <div class="mb-3">
            <label>Item</label>
            <input type="text" value="{{ $item->item_name }}" class="form-control" readonly>
        </div>

        {{-- 🔥 Commission Type --}}
        <div class="mb-3">
            <label>Commission Type</label>
            <select name="commission_type" class="form-control">
                <option value="percentage"
                    {{ $item->commission_type == 'percentage' ? 'selected' : '' }}>
                    Percentage (%)
                </option>

                <option value="fixed"
                    {{ $item->commission_type == 'fixed' ? 'selected' : '' }}>
                    Fixed (₹)
                </option>
            </select>
        </div>

        {{-- 🔥 Commission Value --}}
        <div class="mb-3">
            <label>Commission Value</label>
            <input type="number"
                   step="0.01"
                   name="commission_percent"
                   value="{{ $item->commission_percent }}"
                   class="form-control"
                   placeholder="Enter value">
        </div>

        <button class="btn btn-success">Save</button>

    </form>

</x-app-layout>