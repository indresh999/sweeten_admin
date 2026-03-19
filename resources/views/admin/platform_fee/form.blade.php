<div class="row">

    <div class="col-md-6 mb-3">
        <label>Handling Fee</label>
        <input type="number" step="0.01" name="handling_fee"
               class="form-control @error('handling_fee') is-invalid @enderror"
               value="{{ old('handling_fee', $fee->handling_fee ?? '') }}" required>
        @error('handling_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Packing Fee</label>
        <input type="number" step="0.01" name="packing_fee"
               class="form-control @error('packing_fee') is-invalid @enderror"
               value="{{ old('packing_fee', $fee->packing_fee ?? '') }}" required>
        @error('packing_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Min Order Amount</label>
        <input type="number" step="0.01" name="min_order_amount"
               class="form-control"
               value="{{ old('min_order_amount', $fee->min_order_amount ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Max Order Amount</label>
        <input type="number" step="0.01" name="max_order_amount"
               class="form-control"
               value="{{ old('max_order_amount', $fee->max_order_amount ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Priority</label>
        <input type="number" name="priority"
               class="form-control"
               value="{{ old('priority', $fee->priority ?? 1) }}" required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="1" {{ old('status', $fee->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('status', $fee->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

</div>