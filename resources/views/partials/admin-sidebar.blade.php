<ul class="navbar-nav iq-main-menu" id="sidebar">

    <li class="nav-item static-item"><a class="nav-link static-item disabled" href="#" tabindex="-1"><span class="default-icon">Main</span><span class="mini-icon">-</span></a></li>

    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2Z" fill="currentColor"/><path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 13.4656H7.92449C9.32676 13.4656 10.463 14.6114 10.463 16.0255V19.4401C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.4401V16.0255C2 14.6114 3.13626 13.4656 4.53852 13.4656Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M16.0756 13.4656H19.4616C20.8639 13.4656 22.0001 14.6114 22.0001 16.0255V19.4401C22.0001 20.8532 20.8639 22 19.4616 22H16.0756C14.6734 22 13.5371 20.8532 13.5371 19.4401V16.0255C13.5371 14.6114 14.6734 13.4656 16.0756 13.4656Z" fill="currentColor"/></svg></i>
        <span class="item-name">Dashboard</span>
    </a></li>

    <li class="nav-item static-item mt-2"><a class="nav-link static-item disabled" href="#" tabindex="-1"><span class="default-icon">People</span><span class="mini-icon">-</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#vendor-menu" role="button" aria-expanded="{{ request()->routeIs('admin.vendors.*') ? 'true':'false' }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M2 11.0786C2.10931 6.34486 6.05658 2.37546 10.7912 2.27564L2 11.0786Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M12.0801 14.7529L9.37392 22C9.10623 22.7585 8.06382 22.8288 7.69067 22.1126L2.16357 11.7786L11.0449 7.17552L14.7057 6.10354C17.4587 5.24778 18.4 6.9375 18.5933 8.8C18.8053 10.8503 17.8876 13.1285 16.4091 14.2178C14.9306 15.3071 12.0801 14.7529 12.0801 14.7529Z" fill="currentColor"/></svg></i>
        <span class="item-name">Vendors</span>
        <i class="right-icon"><svg width="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
    </a>
        <ul class="sub-nav collapse {{ request()->routeIs('admin.vendors.*') ? 'show' : '' }}" id="vendor-menu">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.vendors.index') ? 'active' : '' }}" href="{{ route('admin.vendors.index') }}"><i class="sidenav-mini-icon">A</i><span class="item-name">All Vendors</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.vendors.pending') ? 'active' : '' }}" href="{{ route('admin.vendors.pending') }}"><i class="sidenav-mini-icon">P</i><span class="item-name">Pending Approval</span></a></li>
        </ul>
    </li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M11.9488 14.54C8.49886 14.54 5.58386 15.1038 5.58386 17.2795C5.58386 19.4562 8.51386 20.0001 11.9488 20.0001C15.3988 20.0001 18.3138 19.4364 18.3138 17.2606C18.3138 15.084 15.3838 14.54 11.9488 14.54Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M11.949 12.467C14.2851 12.467 16.1699 10.5831 16.1699 8.24803C16.1699 5.91306 14.2851 4.02814 11.949 4.02814C9.61293 4.02814 7.72901 5.91306 7.72901 8.24803C7.72901 10.5831 9.61293 12.467 11.949 12.467Z" fill="currentColor"/></svg></i>
        <span class="item-name">Customers</span>
    </a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.delivery.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#delivery-menu" role="button" aria-expanded="{{ request()->routeIs('admin.delivery.*') ? 'true':'false' }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M19.9927 18.9929H14.2984C13.7429 18.9929 13.2975 19.4403 13.2975 20.0009C13.2975 20.5614 13.7429 21.0088 14.2984 21.0088H19.9927C20.5483 21.0088 20.9937 20.5614 20.9937 20.0009C20.9937 19.4403 20.5483 18.9929 19.9927 18.9929Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M13.8748 17.0854L14.2149 13.7824L16.0194 11.1359C16.5133 10.4209 16.6844 9.55009 16.4823 8.71918L13.6152 5.7704C13.0756 5.37018 12.4136 5.17918 11.7476 5.23318L10.4476 5.33218C9.4676 5.40018 8.6276 5.99118 8.1876 6.88218L6.79461 9.60918C6.39461 10.3962 6.31561 11.3162 6.57061 12.1592L7.24561 14.3862L7.25361 16.6432L13.8748 17.0854Z" fill="currentColor"/></svg></i>
        <span class="item-name">Delivery Boys</span>
        <i class="right-icon"><svg width="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
    </a>
        <ul class="sub-nav collapse {{ request()->routeIs('admin.delivery.*') ? 'show' : '' }}" id="delivery-menu">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.delivery.index') ? 'active' : '' }}" href="{{ route('admin.delivery.index') }}"><i class="sidenav-mini-icon">A</i><span class="item-name">All Boys</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.delivery.docs.*') ? 'active' : '' }}" href="{{ route('admin.delivery.docs.pending') }}"><i class="sidenav-mini-icon">D</i><span class="item-name">Pending Docs</span></a></li>
        </ul>
    </li>

    <li class="nav-item static-item mt-2"><a class="nav-link static-item disabled" href="#" tabindex="-1"><span class="default-icon">Catalogue</span><span class="mini-icon">-</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#catalogue-menu" role="button" aria-expanded="{{ request()->routeIs('admin.categories.*')||request()->routeIs('admin.subcategories.*')||request()->routeIs('admin.items.*') ? 'true':'false' }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M2 7C2 5.89543 2.89543 5 4 5H20C21.1046 5 22 5.89543 22 7V9C22 10.1046 21.1046 11 20 11H4C2.89543 11 2 10.1046 2 9V7Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M2 15C2 13.8954 2.89543 13 4 13H20C21.1046 13 22 13.8954 22 15V17C22 18.1046 21.1046 19 20 19H4C2.89543 19 2 18.1046 2 17V15Z" fill="currentColor"/></svg></i>
        <span class="item-name">Catalogue</span>
        <i class="right-icon"><svg width="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
    </a>
        <ul class="sub-nav collapse {{ request()->routeIs('admin.categories.*')||request()->routeIs('admin.subcategories.*')||request()->routeIs('admin.items.*') ? 'show' : '' }}" id="catalogue-menu">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="sidenav-mini-icon">C</i><span class="item-name">Categories</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}" href="{{ route('admin.subcategories.index') }}"><i class="sidenav-mini-icon">S</i><span class="item-name">Subcategories</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.items.*') ? 'active' : '' }}" href="{{ route('admin.items.index') }}"><i class="sidenav-mini-icon">I</i><span class="item-name">Items</span></a></li>
        </ul>
    </li>

    {{-- Home Layout --}}
    <li class="nav-item"><a class="nav-link {{ request()->is('admin/home-layout') || request()->is('admin/shop-home-layout') ? 'active' : '' }}" data-bs-toggle="collapse" href="#home-layout-menu" role="button" aria-expanded="{{ request()->is('admin/home-layout') || request()->is('admin/shop-home-layout') ? 'true':'false' }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M9.14373 20.7821V17.7152C9.14378 16.9381 9.77586 16.3088 10.5584 16.3088H13.4326C14.2142 16.3088 14.8453 16.9381 14.8453 17.7152V20.7732C14.8453 21.4473 15.3927 21.9951 16.0695 22H18.0505C19.9302 22 21.4543 20.4849 21.4543 18.5921V9.57467C21.4543 8.74951 21.0768 7.96812 20.4351 7.44697L13.735 2.15641C12.4581 1.14453 10.6386 1.14453 9.36173 2.15641L2.62044 7.44697C1.97875 7.96812 1.60126 8.74951 1.60126 9.57467V18.5921C1.60126 20.4849 3.12542 22 5.00505 22H6.98605C7.67903 22 8.23746 21.447 8.23746 20.7634L8.23746 20.7821H9.14373Z" fill="currentColor"/></svg></i>
        <span class="item-name">Home Layout</span>
        <i class="right-icon"><svg width="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
    </a>
        <ul class="sub-nav collapse {{ request()->is('admin/home-layout') || request()->is('admin/shop-home-layout') ? 'show' : '' }}" id="home-layout-menu">
            <li class="nav-item"><a class="nav-link {{ request()->is('admin/home-layout') ? 'active' : '' }}" href="/admin/home-layout"><i class="sidenav-mini-icon">C</i><span class="item-name">Category Layout</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->is('admin/shop-home-layout') ? 'active' : '' }}" href="/admin/shop-home-layout"><i class="sidenav-mini-icon">S</i><span class="item-name">Shop Home Layout</span></a></li>
        </ul>
    </li>

    <li class="nav-item static-item mt-2"><a class="nav-link static-item disabled" href="#" tabindex="-1"><span class="default-icon">Commerce</span><span class="mini-icon">-</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M9.14373 20.7821V17.7152C9.14378 16.9381 9.77586 16.3088 10.5584 16.3088H13.4326C14.2142 16.3088 14.8453 16.9381 14.8453 17.7152V20.7732C14.8453 21.4473 15.3927 21.9951 16.0695 22H18.0505C19.9302 22 21.4543 20.4849 21.4543 18.5921V9.57467C21.4543 8.74951 21.0768 7.96812 20.4351 7.44697L13.735 2.15641C12.4581 1.14453 10.6386 1.14453 9.36173 2.15641L2.62044 7.44697C1.97875 7.96812 1.60126 8.74951 1.60126 9.57467V18.5921C1.60126 20.4849 3.12542 22 5.00505 22H6.98605C7.67903 22 8.23746 21.447 8.23746 20.7634L8.23746 20.7821H9.14373Z" fill="currentColor"/></svg></i>
        <span class="item-name">Orders</span>
    </a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M12.3456 19.3428L18.6801 13.0083C19.44 12.2484 19.44 11.0097 18.6801 10.2498L13.5697 5.13946C13.2054 4.77435 12.7127 4.57031 12.2 4.56934H6.80002C5.79002 4.56934 4.97003 5.38933 4.97003 6.39933V11.7993C4.97003 12.3093 5.18 12.7893 5.55 13.1593L11.0655 18.7033L12.3456 19.3428Z" fill="currentColor"/></svg></i>
        <span class="item-name">Coupons</span>
    </a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M21 4H3C2.44772 4 2 4.44772 2 5V19C2 19.5523 2.44772 20 3 20H21C21.5523 20 22 19.5523 22 19V5C22 4.44772 21.5523 4 21 4Z" fill="currentColor"/></svg></i>
        <span class="item-name">Banners</span>
    </a></li>

    <li class="nav-item static-item mt-2"><a class="nav-link static-item disabled" href="#" tabindex="-1"><span class="default-icon">Finance</span><span class="mini-icon">-</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.earnings.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#earnings-menu" role="button" aria-expanded="{{ request()->routeIs('admin.earnings.*') ? 'true':'false' }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" fill="currentColor"/><path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67V7z" fill="white"/></svg></i>
        <span class="item-name">Earnings</span>
        <i class="right-icon"><svg width="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
    </a>
        <ul class="sub-nav collapse {{ request()->routeIs('admin.earnings.*') ? 'show' : '' }}" id="earnings-menu">
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.earnings.index') }}"><i class="sidenav-mini-icon">O</i><span class="item-name">Overview</span></a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.earnings.platform') }}"><i class="sidenav-mini-icon">P</i><span class="item-name">Platform</span></a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.earnings.vendors') }}"><i class="sidenav-mini-icon">V</i><span class="item-name">Vendors</span></a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.earnings.delivery') }}"><i class="sidenav-mini-icon">D</i><span class="item-name">Delivery Boys</span></a></li>
        </ul>
    </li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.tax.*') ? 'active' : '' }}" href="{{ route('admin.tax.index') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M8 4H5C4.44772 4 4 4.44772 4 5V20C4 20.5523 4.44772 21 5 21H19C19.5523 21 20 20.5523 20 20V5C20 4.44772 19.5523 4 19 4H16" fill="currentColor"/><path d="M8 4C8 3.44772 8.44772 3 9 3H15C15.5523 3 16 3.44772 16 4V5C16 5.55228 15.5523 6 15 6H9C8.44772 6 8 5.55228 8 5V4Z" fill="white"/></svg></i>
        <span class="item-name">Tax & Billing</span>
    </a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M3 3v18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 16l4-4 4 4 4-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
        <span class="item-name">Reports</span>
    </a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.monitor.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#monitor-menu" role="button" aria-expanded="{{ request()->routeIs('admin.monitor.*') ? 'true':'false' }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M2 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10S2 17.52 2 12z" fill="currentColor"/><path d="M8 12l3 3 5-5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
        <span class="item-name">Monitor</span>
        <i class="right-icon"><svg width="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
    </a>
        <ul class="sub-nav collapse {{ request()->routeIs('admin.monitor.*') ? 'show' : '' }}" id="monitor-menu">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.monitor.index') ? 'active' : '' }}" href="{{ route('admin.monitor.index') }}"><i class="sidenav-mini-icon">O</i><span class="item-name">Overview</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.monitor.top-products') ? 'active' : '' }}" href="{{ route('admin.monitor.top-products') }}"><i class="sidenav-mini-icon">P</i><span class="item-name">Top Products</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.monitor.top-shops') ? 'active' : '' }}" href="{{ route('admin.monitor.top-shops') }}"><i class="sidenav-mini-icon">S</i><span class="item-name">Top Shops</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.monitor.location') ? 'active' : '' }}" href="{{ route('admin.monitor.location') }}"><i class="sidenav-mini-icon">L</i><span class="item-name">Location Analytics</span></a></li>
        </ul>
    </li>

    <li class="nav-item static-item mt-2"><a class="nav-link static-item disabled" href="#" tabindex="-1"><span class="default-icon">System</span><span class="mini-icon">-</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M12 2C8.13 2 5 5.13 5 9v7l-2 2v1h18v-1l-2-2V9c0-3.87-3.13-7-7-7z" fill="currentColor"/><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2z" fill="currentColor"/></svg></i>
        <span class="item-name">Push Notifications</span>
    </a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path opacity="0.4" d="M12 15.5A3.5 3.5 0 018.5 12 3.5 3.5 0 0112 8.5a3.5 3.5 0 013.5 3.5 3.5 3.5 0 01-3.5 3.5z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.58 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65z" fill="currentColor"/></svg></i>
        <span class="item-name">Settings</span>
    </a></li>
</ul>
