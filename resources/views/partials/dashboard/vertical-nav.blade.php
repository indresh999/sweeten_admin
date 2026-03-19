<ul class="navbar-nav iq-main-menu" id="sidebar">

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Home</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    {{-- Dashboard --}}
    <li class="nav-item">
        <a class="nav-link {{ activeRoute(route('dashboard')) }}" href="{{ route('dashboard') }}">
            <i class="icon">
                <!-- SVG same -->
            </i>
            <span class="item-name">Dashboard</span>
        </a>
    </li>

    {{-- App Users --}}
    <li class="nav-item mb-2">
        <a class="nav-link {{ activeRoute(route('users.index')) }}" href="{{ route('users.index') }}">
            <span class="item-name">App Users</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

    {{-- Shops --}}
    <li class="nav-item mb-2">
        <a class="nav-link {{ activeRoute(route('shops')) }}" href="{{ route('shops') }}">
            <span class="item-name">Shops</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

    {{-- Orders --}}
    <li class="nav-item mb-2">
        <a class="nav-link {{ activeRoute(route('orders')) }}" href="{{ route('orders') }}">
            <span class="item-name">Orders</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

    {{-- Delivery Boy --}}
    <li class="nav-item mb-2">
        <a class="nav-link {{ activeRoute(route('admin.delivery.boys')) }}" href="{{ route('admin.delivery.boys') }}">
            <span class="item-name">Delivery Boy</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

    {{-- Item Categories --}}
    <li class="nav-item mb-2">
        <a class="nav-link {{ activeRoute('admin.item-categories.index') }}"
           href="{{ route('admin.item-categories.index') }}">
            <span class="item-name">Item Categories</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

    {{-- Item Sub Categories --}}
    <li class="nav-item mb-2">
        <a class="nav-link {{ activeRoute('admin.item-subcategories.index') }}"
           href="{{ route('admin.item-subcategories.index') }}">
            <span class="item-name">Item Sub Categories</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

    {{-- Items --}}
    <li class="nav-item mb-2">
        <a class="nav-link {{ activeRoute('admin.items.index') }}" href="{{ route('admin.items.index') }}">
            <span class="item-name">Items</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

    {{-- Delivery Charge (FIXED) --}}
    <li class="nav-item mb-2">
        <a href="{{ route('delivery-charge.index') }}"
           class="nav-link {{ request()->routeIs('delivery-charge.*') ? 'active' : '' }}">
            <span class="item-name">Delivery Charge</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

    {{-- Platform Fees (FIXED) --}}
    <li class="nav-item mb-2">
        <a href="{{ route('platform-fee.index') }}"
           class="nav-link {{ request()->routeIs('platform-fee.*') ? 'active' : '' }}">
            <span class="item-name">Platform Fees</span>
            <i class="right-icon">➜</i>
        </a>
    </li>

</ul>