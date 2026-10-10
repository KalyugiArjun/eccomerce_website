<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Platform Portal - ShopKart')</title>

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
        .sk-admin-sidebar {
            width: 260px;
            background: #0f172a;
            color: #94a3b8;
            min-height: 100vh;
        }
        .sk-admin-navlink {
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
        .sk-admin-navlink:hover, .sk-admin-navlink.active {
            color: #ffffff;
            background: #1e293b;
        }
        .sk-admin-navlink.active {
            background: #dc2626;
            color: #ffffff;
            font-weight: 600;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light">

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sk-admin-sidebar d-flex flex-column flex-shrink-0 p-3">
        <div class="d-flex align-items-center gap-2 mb-4 px-2">
            <a href="{{ route('home') }}" class="sk-brand text-white fs-4 text-decoration-none">
                <span>Shop</span><span class="badge bg-danger">Admin</span>
            </a>
        </div>

        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="sk-admin-navlink {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.sellers.index') }}" class="sk-admin-navlink {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
                    <i class="bi bi-shop"></i> Sellers & Approval
                </a>
            </li>
            <li>
                <a href="{{ route('admin.products.index') }}" class="sk-admin-navlink {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Products Catalog
                </a>
            </li>
            <li>
                <a href="{{ route('admin.orders.index') }}" class="sk-admin-navlink {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-check"></i> Orders & Split Orders
                </a>
            </li>
            <li>
                <a href="{{ route('admin.commissions.index') }}" class="sk-admin-navlink {{ request()->routeIs('admin.commissions.*') ? 'active' : '' }}">
                    <i class="bi bi-currency-rupee"></i> Commission & Payouts
                </a>
            </li>
            <li>
                <a href="{{ route('admin.categories.index') }}" class="sk-admin-navlink {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i> Categories & Brands
                </a>
            </li>
            <li>
                <a href="{{ route('admin.refunds.index') }}" class="sk-admin-navlink {{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-return-left"></i> Returns & Refunds
                </a>
            </li>
        </ul>

        <hr class="border-secondary">
        <div class="px-2">
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm w-100 mb-2">
                <i class="bi bi-arrow-left"></i> Customer Marketplace
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100">Sign Out</button>
            </form>
        </div>
    </div>

    <!-- Main Admin Content Area -->
    <div class="flex-grow-1 d-flex flex-column min-vh-100 overflow-auto">
        <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center shadow-sm">
            <h5 class="mb-0 fw-bold">@yield('page_title', 'Platform Administration')</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-danger"><i class="bi bi-shield-lock"></i> Super Admin</span>
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
