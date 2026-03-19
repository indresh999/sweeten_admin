<x-app-layout :assets="$assets ?? []">

@section('content')
    <h4>Subcategory Commission</h4>

    <form method="POST" action="{{ route('admin.subcategory.commission.update', $subcategory->id) }}">
        @csrf

        <div class="mb-3">
            <label>Subcategory</label>
            <input type="text" value="{{ $subcategory->name }}" class="form-control" readonly>
        </div>

        {{-- 🔥 Commission Type --}}
        <div class="mb-3">
            <label>Commission Type</label>
            <select name="commission_type" class="form-control">
                <option value="percentage" 
                    {{ $subcategory->commission_type == 'percentage' ? 'selected' : '' }}>
                    Percentage (%)
                </option>

                <option value="fixed" 
                    {{ $subcategory->commission_type == 'fixed' ? 'selected' : '' }}>
                    Fixed (₹)
                </option>
            </select>
        </div>

        {{-- 🔥 Commission Value --}}
        <div class="mb-3">
            <label>Commission Value</label>
            <input type="number" step="0.01"
                   name="commission_percent"
                   value="{{ $subcategory->commission_percent }}"
                   class="form-control"
                   placeholder="Enter value">
        </div>

        <button class="btn btn-success">Save</button>

    </form>

</x-app-layout>