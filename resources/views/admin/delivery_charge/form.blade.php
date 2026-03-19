<div class="mb-3">
    <label>Min Distance (km)</label>
    <input type="number" step="0.01" name="min_distance"
           class="form-control @error('min_distance') is-invalid @enderror"
           value="{{ old('min_distance', $charge->min_distance ?? '') }}" required>
    @error('min_distance') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label>Max Distance (km)</label>
    <input type="number" step="0.01" name="max_distance"
           class="form-control @error('max_distance') is-invalid @enderror"
           value="{{ old('max_distance', $charge->max_distance ?? '') }}" required>
    @error('max_distance') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label>Charge Amount (₹)</label>
    <input type="number" step="0.01" name="charge_amount"
           class="form-control @error('charge_amount') is-invalid @enderror"
           value="{{ old('charge_amount', $charge->charge_amount ?? '') }}" required>
    @error('charge_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label>Free Above Amount (₹)</label>
    <input type="number" step="0.01" name="free_above_amount"
           class="form-control @error('free_above_amount') is-invalid @enderror"
           value="{{ old('free_above_amount', $charge->free_above_amount ?? '') }}">
    @error('free_above_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label>Priority</label>
    <input type="number" name="priority"
           class="form-control @error('priority') is-invalid @enderror"
           value="{{ old('priority', $charge->priority ?? 1) }}" required>
    @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label>Status</label>
    <select name="status" class="form-control">
        <option value="1" {{ old('status', $charge->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('status', $charge->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
    </select>
</div>