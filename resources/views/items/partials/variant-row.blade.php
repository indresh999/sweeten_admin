<div class="row mb-2 variant-row">
<input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id ?? '' }}">

<div class="col-md-3">
<input name="variants[{{ $index }}][label]" class="form-control"
value="{{ $variant->label ?? '' }}" placeholder="Label" required>
</div>

<div class="col-md-2">
<input name="variants[{{ $index }}][price]" type="number" step="0.01"
value="{{ $variant->price ?? '' }}" class="form-control" required>
</div>

<div class="col-md-2">
<input name="variants[{{ $index }}][offer_price]" type="number" step="0.01"
value="{{ $variant->offer_price ?? '' }}" class="form-control">
</div>

<div class="col-md-2">
<input name="variants[{{ $index }}][gst_percent]" type="number" step="0.01"
value="{{ $variant->gst_percent ?? '' }}" class="form-control" required>
</div>

<div class="col-md-2">
<input name="variants[{{ $index }}][hsn_code]" class="form-control"
value="{{ $variant->hsn_code ?? '' }}">
</div>

<div class="col-md-1">
<button type="button" class="btn btn-danger btn-sm"
onclick="this.closest('.variant-row').remove()">✕</button>
</div>
</div>