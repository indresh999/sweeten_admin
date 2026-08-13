<nav class="nav navbar navbar-expand-xl navbar-light iq-navbar">
    <div class="container-fluid navbar-inner">
        {{-- Mobile Hamburger --}}
        <button class="sidebar-toggle-btn d-xl-none me-2" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <a href="{{ route('admin.dashboard') }}" class="navbar-brand d-xl-none">
            <svg width="28" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2" transform="rotate(-45 -0.757324 19.2427)" fill="currentColor"/>
                <rect x="7.72803" y="27.728" width="28" height="4" rx="2" transform="rotate(-45 7.72803 27.728)" fill="currentColor"/>
                <rect x="10.5366" y="16.3945" width="16" height="4" rx="2" transform="rotate(45 10.5366 16.3945)" fill="currentColor"/>
                <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2" transform="rotate(45 10.5562 -0.556152)" fill="currentColor"/>
            </svg>
        </a>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="py-0 nav-link d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('images/avatars/01.png') }}" alt="User-Profile" class="theme-color-default-img img-fluid avatar avatar-40 avatar-rounded">
                        <div class="caption ms-3 d-none d-md-block">
                            <h6 class="mb-0 caption-title">{{ auth()->user()->full_name ?? 'Admin' }}</h6>
                            <p class="mb-0 caption-sub-title">{{ ucfirst(auth()->user()->user_type ?? 'Administrator') }}</p>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end me-2 me-lg-0" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('users.show', auth()->id() ?? 1) }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('auth.userprivacysetting') }}"><i class="fas fa-shield-alt me-2"></i>Privacy Setting</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="javascript:void(0)" class="dropdown-item"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>{{ __('Log out') }}
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
