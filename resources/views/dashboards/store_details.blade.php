<x-app-layout :assets="$assets ?? []">
   <div>
      <?php $id = $id ?? null; ?>
     

      <div class="row">
         {{-- Left Side Card --}}
         <div class="col-xl-3 col-lg-4">
            <div class="card">
               <div class="card-header d-flex justify-content-between">
                  <div class="header-title">
                     <h4 class="card-title">{{ $id !== null ? 'Update' : 'Add New' }} Shop</h4>
                  </div>
               </div>
               <div class="card-body">
                  
                  {{-- Featured / Profile Image --}}
                  <div class="form-group">
                     <div class="profile-img-edit position-relative">
                        <img src="{{ isset($shop) && $shop->images->first() ? asset($shop->images->first()->image_path) : asset('https://via.placeholder.com/100x100?text=No+Image') }}" 
                             alt="Shop Image" class="profile-pic rounded avatar-100">
                        <div class="upload-icone bg-primary">
                           <svg class="upload-button" width="14" height="14" viewBox="0 0 24 24">
                              <path fill="#ffffff" d="M14.06,9L15,9.94L5.92,19H5V18.08L14.06,9M17.66,3C17.41,3 17.15,3.1 16.96,3.29L15.13,5.12L18.88,8.87L20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18.17,3.09 17.92,3 17.66,3M14.06,6.19L3,17.25V21H6.75L17.81,9.94L14.06,6.19Z" />
                           </svg>
                           <input class="file-upload" type="file" name="images[]" accept="image/*" multiple>
                        </div>
                     </div>
                     <small class="text-muted mt-2 d-block">Upload multiple images (.jpg, .png, .jpeg allowed)</small>
                  </div>

                  {{-- Status --}}
                  <div class="form-group mt-4">
                     <label class="form-label fw-bold">Shop Status:</label>
                     <div class="grid" style="--bs-gap: 1rem">
                        @foreach (['active', 'pending', 'inactive'] as $status)
                           <div class="form-check g-col-6">
                              {{ Form::radio('status', $status, old('status', $shop->status ?? 'active') === $status, ['class' => 'form-check-input', 'id' => 'status-' . $status]) }}
                              <label class="form-check-label text-capitalize" for="status-{{ $status }}">{{ ucfirst($status) }}</label>
                           </div>
                        @endforeach
                     </div>
                  </div>
               </div>
            </div>
         </div>

         {{-- Right Side Card --}}
         <div class="col-xl-9 col-lg-8">
            <div class="card">
               <div class="card-header d-flex justify-content-between">
                  <div class="header-title">
                     <h4 class="card-title">{{ $id !== null ? 'Update' : 'New' }} Shop Information</h4>
                  </div>
                  {{-- <div class="card-action">
                     <a href="{{ route('shops.index') }}" class="btn btn-sm btn-primary" role="button">Back</a>
                  </div> --}}
               </div>

               <div class="card-body">
                  <div class="new-user-info">
                     <div class="row">

                        <div class="form-group col-md-6">
                           <label class="form-label fw-bold">Restaurant Name: <span class="text-danger">*</span></label>
                           {{ Form::text('restaurant_name', old('restaurant_name', $shop->restaurant_name ?? ''), ['class' => 'form-control', 'required', 'placeholder' => 'Enter restaurant name']) }}
                        </div>

                        <div class="form-group col-md-6">
                           <label class="form-label fw-bold">Email:</label>
                           {{ Form::email('email', old('email', $shop->email ?? ''), ['class' => 'form-control', 'placeholder' => 'Email']) }}
                        </div>

                        <div class="form-group col-md-6">
                           <label class="form-label fw-bold">Phone Number:</label>
                           {{ Form::text('phone_number', old('phone_number', $shop->phone_number ?? ''), ['class' => 'form-control', 'placeholder' => 'Phone Number']) }}
                        </div>

                        <div class="form-group col-md-6">
                           <label class="form-label fw-bold">GST Number:</label>
                           {{ Form::text('gst_number', old('gst_number', $shop->gst_number ?? ''), ['class' => 'form-control', 'placeholder' => 'GST Number']) }}
                        </div>

                        <div class="form-group col-md-6">
                           <label class="form-label fw-bold">PAN Number:</label>
                           {{ Form::text('pan_number', old('pan_number', $shop->pan_number ?? ''), ['class' => 'form-control', 'placeholder' => 'PAN Number']) }}
                        </div>

                        <div class="form-group col-md-12">
                           <label class="form-label fw-bold">Restaurant Address:</label>
                           {{ Form::textarea('restaurant_address', old('restaurant_address', $shop->restaurant_address ?? ''), ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Address']) }}
                        </div>

                        <div class="form-group col-md-4">
                           <label class="form-label fw-bold">City:</label>
                           {{ Form::text('city', old('city', $shop->city ?? ''), ['class' => 'form-control', 'placeholder' => 'City']) }}
                        </div>

                        <div class="form-group col-md-4">
                           <label class="form-label fw-bold">State:</label>
                           {{ Form::text('state', old('state', $shop->state ?? ''), ['class' => 'form-control', 'placeholder' => 'State']) }}
                        </div>

                        <div class="form-group col-md-4">
                           <label class="form-label fw-bold">Zip Code:</label>
                           {{ Form::text('zip_code', old('zip_code', $shop->zip_code ?? ''), ['class' => 'form-control', 'placeholder' => 'Zip Code']) }}
                        </div>

                        <div class="form-group col-md-12">
                           <label class="form-label fw-bold">Country:</label>
                           {{ Form::text('country', old('country', $shop->country ?? ''), ['class' => 'form-control', 'placeholder' => 'Country']) }}
                        </div>
                     </div>

                     <hr>
                     <div class="text-end">
                        <button type="submit" class="btn btn-primary">{{ $id !== null ? 'Save Changes' : 'Submit' }}</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      {!! Form::close() !!}
   </div>
</x-app-layout>