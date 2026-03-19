<x-app-layout :assets="$assets ?? []">

@section('content')
    <h4>Category Commission</h4>

    <form method="POST" action="{{ route('admin.category.commission.update', $category->id) }}">
        @csrf

        <div class="mb-3">
            <label>Category</label>
            <input type="text" class="form-control" value="{{ $category->category_name }}" readonly>
        </div>

        {{-- 🔥 Commission Type --}}
        <div class="mb-3">
            <label>Commission Type</label>
            <select name="commission_type" class="form-control">
                <option value="percentage" {{ $category->commission_type == 'percentage' ? 'selected' : '' }}>
                    Percentage (%)
                </option>
                <option value="fixed" {{ $category->commission_type == 'fixed' ? 'selected' : '' }}>
                    Fixed (₹)
                </option>
            </select>
        </div>

        {{-- 🔥 Commission Value --}}
        <div class="mb-3">
            <label>Commission Value</label>
            <input type="number" step="0.01" name="commission_percent"
                   value="{{ $category->commission_percent }}"
                   class="form-control"
                   placeholder="Enter value">
        </div>

        <button class="btn btn-success">Save</button>

    </form>
</x-app-layout>