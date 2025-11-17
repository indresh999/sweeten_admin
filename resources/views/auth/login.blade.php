<x-guest-layout>
   <section class="login-content">
      <div class="row m-0 align-items-center bg-white vh-100">
         <div class="col-md-6">
            <div class="row justify-content-center">
               <div class="">
                  <div class="card card-transparent shadow-none d-flex justify-content-center mb-0 auth-card">
                     <div class="card-body">
                        <a href="{{route('dashboard')}}" class="navbar-brand d-flex align-items-center mb-3">
                           
                         
                        </a>
                       
                        <h2 class="mb-2 text-center">Sweeten Admin Panel</h2>
                        <p class="text-center">Login to stay connected.</p>
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <!-- Validation Errors -->
                        <x-auth-validation-errors class="mb-4" :errors="$errors" />
                        <form method="POST" action="{{ route('login') }}" data-toggle="validator">
                            {{csrf_field()}}
                           <div class="row">
                              <div class="col-lg-12">
                                 <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input id="email" type="email" name="email"  value="{{env('IS_DEMO') ? 'admin@example.com' : old('email')}}"   class="form-control"  placeholder="admin@example.com" required autofocus>
                                 </div>
                              </div>
                              <div class="col-lg-12">
                                 <div class="form-group">
                                    <label for="password" class="form-label">Password</label>
                                    <input class="form-control" type="password" placeholder="********"  name="password" value="{{ env('IS_DEMO') ? 'password' : '' }}" required autocomplete="current-password">
                                 </div>
                              </div>
                              <div class="col-lg-12 d-flex justify-content-between">
                                 <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="customCheck1">
                                    <!-- <input type="checkbox" class="custom-control-input" id="customCheck1"> -->
                                    <label class="form-check-label" for="customCheck1">Remember Me</label>
                                 </div>
                                 <a href="{{route('auth.recoverpw')}}"  class="float-end">Forgot Password?</a>
                              </div>
                              {{-- <div class="col-lg-6">
                                 <a href="{{route('auth.recoverpw')}}"  class="float-end">Forgot Password?</a>
                              </div> --}}
                           </div>
                           <div class="d-flex justify-content-center">
                              <button type="submit" class="btn btn-primary">{{ __('Sign In') }}</button>
                           </div>
                          
                          
                           <p class="mt-3 text-center">
                              Don’t have an account? <a href="{{route('auth.signup')}}" class="text-underline">Click here to sign up.</a>
                           </p>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
            <div class="sign-bg">
               
            </div>
         </div>
         <div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden">
            <img src="{{asset('https://thumbs.dreamstime.com/b/abstract-background-green-color-7872833.jpg')}}" class="img-fluid gradient-main animated-scaleX" alt="images">
         </div>
      </div>
   </section>
</x-guest-layout>
