<aside class="sidebar sidebar-default navs-rounded-all sidebar-base" id="appSidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
            <svg width="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2" transform="rotate(-45 -0.757324 19.2427)" fill="currentColor"/>
                <rect x="7.72803" y="27.728" width="28" height="4" rx="2" transform="rotate(-45 7.72803 27.728)" fill="currentColor"/>
                <rect x="10.5366" y="16.3945" width="16" height="4" rx="2" transform="rotate(45 10.5366 16.3945)" fill="currentColor"/>
                <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2" transform="rotate(45 10.5562 -0.556152)" fill="currentColor"/>
            </svg>
            <h4 class="logo-title">{{ env('APP_NAME') }}</h4>
        </a>
        <button class="sidebar-close-btn d-xl-none" id="closeSidebar" aria-label="Close sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="sidebar-body pt-0 data-scrollbar">
        <div class="sidebar-list" id="sidebar">
            @include('partials.admin-sidebar')
        </div>
    </div>
    <div class="sidebar-footer">
        <div class="sidebar-user-info d-none d-xl-flex">
            <img src="{{ asset('images/avatars/01.png') }}" alt="Admin" class="avatar avatar-40 rounded-circle">
            <div class="ms-2">
                <p class="mb-0 small fw-semibold text-dark">{{ auth()->user()->full_name ?? 'Admin' }}</p>
                <p class="mb-0 small text-muted">{{ ucfirst(auth()->user()->user_type ?? 'Administrator') }}</p>
            </div>
        </div>
    </div>
</aside>
