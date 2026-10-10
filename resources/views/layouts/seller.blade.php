<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seller Portal - ShopKart')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/shopkart.css') }}">
    <style>
        .sk-seller-sidebar {
            width: 260px;
            background: #0f172a;
            color: #94a3b8;
            min-height: 100vh;
        }
        .sk-seller-navlink {
            color: #94a3b8;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.2s;
        }
        .sk-seller-navlink:hover, .sk-seller-navlink.active {
            color: #ffffff;
            background: #1e293b;
        }
        .sk-seller-navlink.active {
            background: #f97316;
            color: #ffffff;
            font-weight: 600;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light">

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sk-seller-sidebar d-flex flex-column flex-shrink-0 p-3">
        <div class="d-flex align-items-center gap-2 mb-4 px-2">
            <a href="{{ route('home') }}" class="sk-brand text-white fs-4 text-decoration-none">
                <span>Shop</span><span class="sk-brand-badge">Seller</span>
            </a>
        </div>

        @php $seller = Auth::user()->seller; @endphp
        @if($seller)
            <div class="px-2 py-3 mb-3 bg-dark rounded border border-secondary text-truncate">
                <div class="fw-bold text-white small text-truncate">{{ $seller->store_name }}</div>
                <small class="text-warning"><i class="bi bi-star-fill"></i> {{ $seller->rating }} &bull; {{ $seller->trust_score }}% Trust</small>
            </div>
        @endif

        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a href="{{ route('seller.dashboard') }}" class="sk-seller-navlink {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('seller.products.index') }}" class="sk-seller-navlink {{ request()->routeIs('seller.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Products
                </a>
            </li>
            <li>
                <a href="{{ route('seller.orders.index') }}" class="sk-seller-navlink {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-check"></i> Orders & Dispatch
                </a>
            </li>
            <li>
                <a href="{{ route('seller.inventory.index') }}" class="sk-seller-navlink {{ request()->routeIs('seller.inventory.*') ? 'active' : '' }}">
                    <i class="bi bi-archive"></i> Inventory
                </a>
            </li>
            <li>
                <a href="{{ route('seller.payouts.index') }}" class="sk-seller-navlink {{ request()->routeIs('seller.payouts.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i> Earnings & Payouts
                </a>
            </li>
            <li>
                <a href="{{ route('seller.settings') }}" class="sk-seller-navlink {{ request()->routeIs('seller.settings') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Store Settings
                </a>
            </li>
            @if($seller)
                <li>
                    <a href="{{ route('seller.storefront', $seller->slug) }}" target="_blank" class="sk-seller-navlink text-warning">
                        <i class="bi bi-box-arrow-up-right"></i> View Public Store
                    </a>
                </li>
            @endif
        </ul>

        <hr class="border-secondary">
        <div class="px-2">
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm w-100 mb-2">
                <i class="bi bi-arrow-left"></i> Customer Site
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100">Sign Out</button>
            </form>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-grow-1 d-flex flex-column min-vh-100 overflow-auto">
        <!-- Top Navbar -->
        <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center shadow-sm">
            <h5 class="mb-0 fw-bold">@yield('page_title', 'Seller Portal')</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved Merchant</span>
                <span class="small text-muted">{{ Auth::user()->name }}</span>
            </div>
        </header>

        <div class="p-4 flex-grow-1">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
</body>
</html>
