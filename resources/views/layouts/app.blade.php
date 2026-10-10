<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "ShopKart - India's Multi-Vendor Marketplace | Shop Local • Fast Delivery")</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Leaflet CSS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Custom ShopKart Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/shopkart.css') }}">

    @stack('styles')
</head>
<body>

    <!-- 1. Top Announcement & Location Bar -->
    <div class="sk-topbar d-none d-md-block">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span class="d-flex align-items-center gap-1 cursor-pointer" data-bs-toggle="modal" data-bs-target="#locationModal" role="button">
                        <i class="bi bi-geo-alt-fill text-warning"></i>
                        Deliver to: <strong>{{ request()->cookie('shopkart_city', Auth::user()?->preferred_city ?? 'Lucknow') }} - {{ request()->cookie('shopkart_pincode', Auth::user()?->preferred_pincode ?? '226001') }}</strong>
                        <span class="text-warning text-decoration-underline ms-1">(Change)</span>
                    </span>
                    <span class="text-secondary">|</span>
                    <span><i class="bi bi-shield-check text-success"></i> 100% Verified Indian Merchants</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('fast-delivery.index') }}"><i class="bi bi-lightning-charge-fill text-warning"></i> Fast Delivery (Under 30m)</a>
                    <a href="{{ route('group-buying.index') }}"><i class="bi bi-people-fill text-info"></i> Group Buy & Save</a>
                    @if(!Auth::check() || !Auth::user()->isSeller())
                        <a href="{{ route('seller.register') }}" class="text-warning fw-semibold"><i class="bi bi-shop"></i> Sell on ShopKart</a>
                    @endif
                    <a href="{{ route('nearby.index') }}"><i class="bi bi-map"></i> Nearby Stores</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Main Marketplace Navbar -->
    <nav class="sk-navbar sticky-top">
        <div class="container">
            <div class="row align-items-center gy-2">
                <!-- Brand Logo -->
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="{{ route('home') }}" class="sk-brand">
                        <span>Shop</span><span class="sk-brand-badge">Kart</span>
                    </a>
                </div>

                <!-- Global Search with Visual Search & AI buttons -->
                <div class="col-12 col-md-6 col-lg-6 order-3 order-md-2">
                    <form action="{{ route('products.index') }}" method="GET" class="sk-search-bar d-flex align-items-center">
                        <input type="text" name="q" class="form-control flex-grow-1" placeholder="Search 50,000+ local products, electronics, kurtas, shoes..." value="{{ request('q') }}">
                        <a href="{{ route('visual.index') }}" class="btn text-secondary px-2" title="Search by Photo / Camera">
                            <i class="bi bi-camera-fill text-primary"></i>
                        </a>
                        <a href="{{ route('ai.index') }}" class="btn text-secondary px-2" title="Ask ShopKart AI Shopping Assistant">
                            <i class="bi bi-robot text-danger"></i>
                        </a>
                        <button type="submit" class="btn">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Action Nav (Cart, Wishlist, Account) -->
                <div class="col-6 col-md-3 col-lg-4 text-end order-2 order-md-3">
                    <div class="d-inline-flex align-items-center gap-3">
                        <!-- AI Assistant Shortcut -->
                        <a href="{{ route('ai.index') }}" class="btn btn-outline-danger btn-sm d-none d-lg-inline-flex align-items-center gap-1">
                            <i class="bi bi-robot"></i> Ask AI
                        </a>

                        <!-- Wishlist -->
                        <a href="{{ route('wishlist.index') }}" class="position-relative text-dark text-decoration-none p-1" title="Wishlist">
                            <i class="bi bi-heart fs-4"></i>
                            @auth
                                @php $wishCount = Auth::user()->wishlists()->first()?->items()->count() ?? 0; @endphp
                                @if($wishCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        {{ $wishCount }}
                                    </span>
                                @endif
                            @endauth
                        </a>

                        <!-- Cart -->
                        <a href="{{ route('cart.index') }}" class="position-relative text-dark text-decoration-none p-1" title="Cart">
                            <i class="bi bi-cart3 fs-4"></i>
                            @php
                                $cartCount = 0;
                                if(Auth::check()) {
                                    $cartCount = Auth::user()->cart?->items_count ?? 0;
                                } else {
                                    $sessionCart = \App\Models\Cart::where('session_id', session()->getId())->first();
                                    $cartCount = $sessionCart?->items_count ?? 0;
                                }
                            @endphp
                            @if($cartCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark fw-bold" style="font-size: 0.65rem;">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>

                        <!-- Account Dropdown or Sign In / Register Buttons -->
                        @auth
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i>
                                    <span class="d-none d-sm-inline">{{ Str::limit(Auth::user()->name, 12) }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li class="px-3 py-2 bg-light border-bottom">
                                        <div class="fw-bold">{{ Auth::user()->name }}</div>
                                        <small class="text-muted">{{ Auth::user()->email }}</small>
                                        <div class="mt-1"><span class="badge bg-secondary text-uppercase">{{ Auth::user()->role }}</span></div>
                                    </li>
                                    @if(Auth::user()->isAdmin())
                                        <li><a class="dropdown-item fw-semibold text-danger" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Admin Portal</a></li>
                                    @endif
                                    @if(Auth::user()->isSeller())
                                        <li><a class="dropdown-item fw-semibold text-warning" href="{{ route('seller.dashboard') }}"><i class="bi bi-shop me-2"></i> Seller Dashboard</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-box-seam me-2"></i> My Orders & Tracking</a></li>
                                    <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="bi bi-heart me-2"></i> Social Wishlist</a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i> Account Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @else
                            <div class="d-inline-flex align-items-center gap-2">
                                <a href="{{ route('login') }}" class="btn btn-outline-dark btn-sm px-3 fw-semibold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                                </a>

                                <div class="dropdown">
                                    <button class="btn btn-sk-primary btn-sm px-3 fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-person-plus-fill me-1"></i> Register
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="min-width: 240px;">
                                        <li class="px-3 py-1">
                                            <span class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">New to ShopKart?</span>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('register') }}">
                                                <div class="bg-primary-subtle text-primary p-2 rounded-circle">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small">Customer Account</div>
                                                    <small class="text-muted" style="font-size: 0.75rem;">Fast delivery & local deals</small>
                                                </div>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('seller.register') }}">
                                                <div class="bg-warning-subtle text-dark p-2 rounded-circle">
                                                    <i class="bi bi-shop"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small">Seller Registration</div>
                                                    <small class="text-muted" style="font-size: 0.75rem;">Sell your products across India</small>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- 3. Category Navigation Strip -->
    <div class="sk-cat-strip">
        <div class="container d-flex align-items-center">
            <a href="{{ route('products.index') }}" class="sk-cat-link {{ !request('category') ? 'fw-bold' : '' }}">
                <i class="bi bi-grid me-1"></i> All Products
            </a>
            @php $navCategories = \App\Models\Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->take(10)->get(); @endphp
            @foreach($navCategories as $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="sk-cat-link {{ request('category') === $cat->slug ? 'active' : '' }}">
                    @if($cat->icon)<i class="bi {{ $cat->icon }} me-1"></i>@endif {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Flash Notifications -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div>{{ session('warning') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Main Page Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Location Modal -->
    <div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="locationModalLabel"><i class="bi bi-geo-alt-fill text-warning me-2"></i> Select Delivery Location</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('set-location') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-muted small">Choose your city or enter your 6-digit Indian PIN code to discover verified nearby merchants with ultra-fast local delivery.</p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">City</label>
                            <select name="city" id="locationCitySelect" class="form-select" onchange="autoFillPincode(this.value)">
                                <option value="Lucknow" {{ request()->cookie('shopkart_city') == 'Lucknow' ? 'selected' : '' }}>Lucknow, Uttar Pradesh</option>
                                <option value="New Delhi" {{ request()->cookie('shopkart_city') == 'New Delhi' ? 'selected' : '' }}>New Delhi, Delhi NCR</option>
                                <option value="Mumbai" {{ request()->cookie('shopkart_city') == 'Mumbai' ? 'selected' : '' }}>Mumbai, Maharashtra</option>
                                <option value="Bengaluru" {{ request()->cookie('shopkart_city') == 'Bengaluru' ? 'selected' : '' }}>Bengaluru, Karnataka</option>
                                <option value="Jaipur" {{ request()->cookie('shopkart_city') == 'Jaipur' ? 'selected' : '' }}>Jaipur, Rajasthan</option>
                                <option value="Hyderabad" {{ request()->cookie('shopkart_city') == 'Hyderabad' ? 'selected' : '' }}>Hyderabad, Telangana</option>
                                <option value="Chennai" {{ request()->cookie('shopkart_city') == 'Chennai' ? 'selected' : '' }}>Chennai, Tamil Nadu</option>
                                <option value="Pune" {{ request()->cookie('shopkart_city') == 'Pune' ? 'selected' : '' }}>Pune, Maharashtra</option>
                                <option value="Kolkata" {{ request()->cookie('shopkart_city') == 'Kolkata' ? 'selected' : '' }}>Kolkata, West Bengal</option>
                                <option value="Ahmedabad" {{ request()->cookie('shopkart_city') == 'Ahmedabad' ? 'selected' : '' }}>Ahmedabad, Gujarat</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="pincode" id="locationPincodeInput" class="form-control" value="{{ request()->cookie('shopkart_pincode', '226001') }}" required pattern="^[1-9][0-9]{5}$" placeholder="e.g. 226001">
                        </div>

                        <input type="hidden" name="latitude" id="geoLat" value="26.8500">
                        <input type="hidden" name="longitude" id="geoLng" value="80.9499">

                        <button type="button" class="btn btn-outline-secondary w-100 btn-sm mb-2" onclick="detectGPSLocation()">
                            <i class="bi bi-crosshair me-1"></i> Detect My Current GPS Location
                        </button>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sk-primary btn-sm px-4">Save & Discover</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modern Indian Marketplace Footer -->
    <footer class="sk-footer mt-5">
        <div class="container">
            <!-- Trust Pillars Bar -->
            <div class="row pb-4 mb-4 border-bottom border-secondary text-center text-md-start gy-3">
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-3 justify-content-center justify-content-md-start">
                        <i class="bi bi-shield-check text-warning fs-1"></i>
                        <div>
                            <div class="text-white fw-bold">100% Genuine</div>
                            <small class="text-muted">Verified Indian Sellers</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-3 justify-content-center justify-content-md-start">
                        <i class="bi bi-lightning-charge-fill text-warning fs-1"></i>
                        <div>
                            <div class="text-white fw-bold">Local Fast Delivery</div>
                            <small class="text-muted">Under 30-45 Minutes</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-3 justify-content-center justify-content-md-start">
                        <i class="bi bi-credit-card-2-front text-warning fs-1"></i>
                        <div>
                            <div class="text-white fw-bold">Secure Indian Payments</div>
                            <small class="text-muted">UPI, Cards, Razorpay & COD</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-3 justify-content-center justify-content-md-start">
                        <i class="bi bi-arrow-repeat text-warning fs-1"></i>
                        <div>
                            <div class="text-white fw-bold">7-Day Easy Returns</div>
                            <small class="text-muted">Doorstep Pickup</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Links Columns -->
            <div class="row gy-4">
                <div class="col-12 col-md-4">
                    <div class="sk-brand text-white mb-3">
                        <span>Shop</span><span class="sk-brand-badge">Kart</span>
                    </div>
                    <p class="text-muted pe-md-4">
                        ShopKart is India's premier multi-vendor marketplace connecting local neighborhood stores and verified online merchants with fast local delivery, transparent price comparisons, and group-buying deals.
                    </p>
                    <div class="d-flex gap-3 fs-5 text-white">
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="sk-footer-title">Shop Local</div>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="{{ route('nearby.index') }}">Nearby Sellers</a></li>
                        <li><a href="{{ route('fast-delivery.index') }}">Fast Delivery Store</a></li>
                        <li><a href="{{ route('group-buying.index') }}">Group Buy Deals</a></li>
                        <li><a href="{{ route('ai.index') }}">Ask ShopKart AI</a></li>
                        <li><a href="{{ route('visual.index') }}">Visual Image Search</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-2">
                    <div class="sk-footer-title">Seller Hub</div>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="{{ route('seller.register') }}">Sell on ShopKart</a></li>
                        <li><a href="{{ route('seller.dashboard') }}">Seller Portal</a></li>
                        <li><a href="#">Commission Policy</a></li>
                        <li><a href="#">Fulfillment Guidelines</a></li>
                        <li><a href="#">Merchant Support</a></li>
                    </ul>
                </div>
                <div class="col-12 col-md-4">
                    <div class="sk-footer-title">Major Indian Hubs</div>
                    <p class="text-muted small mb-2">Live fast local deliveries across:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary">Lucknow</span>
                        <span class="badge bg-secondary">New Delhi</span>
                        <span class="badge bg-secondary">Mumbai</span>
                        <span class="badge bg-secondary">Bengaluru</span>
                        <span class="badge bg-secondary">Jaipur</span>
                        <span class="badge bg-secondary">Hyderabad</span>
                        <span class="badge bg-secondary">Chennai</span>
                        <span class="badge bg-secondary">Kolkata</span>
                        <span class="badge bg-secondary">Pune</span>
                        <span class="badge bg-secondary">Ahmedabad</span>
                    </div>
                </div>
            </div>

            <div class="row pt-4 mt-4 border-top border-secondary text-muted small">
                <div class="col-md-6 text-center text-md-start">
                    &copy; {{ date('Y') }} ShopKart India Multi-Vendor Marketplace. All rights reserved.
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <span>Designed for Indian E-Commerce • Made with <i class="bi bi-heart-fill text-danger"></i> in India</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Leaflet JS for OpenStreetMap -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const cityPincodes = {
            'Lucknow': { pin: '226001', lat: 26.8500, lng: 80.9499 },
            'New Delhi': { pin: '110001', lat: 28.5494, lng: 77.2536 },
            'Mumbai': { pin: '400001', lat: 19.0860, lng: 72.8890 },
            'Bengaluru': { pin: '560001', lat: 12.9716, lng: 77.5946 },
            'Jaipur': { pin: '302001', lat: 26.9124, lng: 75.7873 },
            'Hyderabad': { pin: '500001', lat: 17.3616, lng: 78.4747 },
            'Chennai': { pin: '600001', lat: 13.0418, lng: 80.2341 },
            'Pune': { pin: '411001', lat: 18.5204, lng: 73.8567 },
            'Kolkata': { pin: '700001', lat: 22.5600, lng: 88.3500 },
            'Ahmedabad': { pin: '380001', lat: 23.0373, lng: 72.5614 },
        };

        function autoFillPincode(city) {
            if (cityPincodes[city]) {
                document.getElementById('locationPincodeInput').value = cityPincodes[city].pin;
                document.getElementById('geoLat').value = cityPincodes[city].lat;
                document.getElementById('geoLng').value = cityPincodes[city].lng;
            }
        }

        function detectGPSLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    document.getElementById('geoLat').value = pos.coords.latitude;
                    document.getElementById('geoLng').value = pos.coords.longitude;
                    alert('GPS location acquired: ' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4));
                }, function (err) {
                    alert('Could not access GPS. Please choose city from the dropdown.');
                });
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
