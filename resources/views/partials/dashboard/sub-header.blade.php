<div class="iq-navbar-header iq-sub-header" style="height: auto;">
    <div class="container-fluid iq-container">
        <div class="flex-wrap d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1 fw-bold text-white">Hello, {{ explode(' ', auth()->user()->full_name ?? 'Admin')[0] }}!</h1>
                <p class="mb-0 text-white-50">Welcome to your dashboard</p>
            </div>
        </div>
    </div>
    <div class="iq-header-img">
        <img src="{{ asset('https://static.vecteezy.com/system/resources/previews/036/230/410/large_2x/ai-generated-pista-green-background-free-photo.jpg') }}" alt="header" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{ asset('/images/dashboard/top-header1.png') }}" alt="header" class="theme-color-purple-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{ asset('/images/dashboard/top-header2.png') }}" alt="header" class="theme-color-blue-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{ asset('/images/dashboard/top-header3.png') }}" alt="header" class="theme-color-green-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{ asset('/images/dashboard/top-header4.png') }}" alt="header" class="theme-color-yellow-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{ asset('/images/dashboard/top-header5.png') }}" alt="header" class="theme-color-pink-img img-fluid w-100 h-100 animated-scaleX">
    </div>
</div>
