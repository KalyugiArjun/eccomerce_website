@extends('layouts.app')

@section('title', 'Sign In - ShopKart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="sk-brand mb-2">
                        <span>Shop</span><span class="sk-brand-badge">Kart</span>
                    </div>
                    <h3 class="fw-bold mb-1">Sign In to Your Account</h3>
                    <p class="text-muted small">Access your orders, wishlist, and fast local deliveries</p>
                </div>

                {{-- Error Alert Banner --}}
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                        <div class="small">
                            <strong>Login Failed:</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="loginEmail" class="form-control border-start-0 ps-0" value="{{ old('email', 'user@shopkart.in') }}" required autofocus placeholder="name@example.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small fw-bold text-dark mb-0">Password</label>
                        </div>
                        <div class="input-group mt-1">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="loginPassword" class="form-control border-start-0 border-end-0 ps-0" value="password123" required placeholder="Enter your password">
                            <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePasswordVisibility()">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" checked>
                            <label class="form-check-label small text-muted" for="rememberMe">Remember Me</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sk-primary w-100 py-2 fw-bold fs-6 mb-3 shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In &rarr;
                    </button>

                    <div class="text-center small py-2 bg-light rounded-3 mb-3">
                        <span>New to ShopKart? </span>
                        <a href="{{ route('register') }}" class="text-warning fw-bold text-decoration-none">Register as Customer</a>
                        <span class="text-muted mx-1">|</span>
                        <a href="{{ route('seller.register') }}" class="text-primary fw-bold text-decoration-none">Sell on ShopKart</a>
                    </div>
                </form>

                <!-- 1-Click Demo Login Shortcuts -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            ⚡ 1-Click Instant Demo Login:
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">Click to Auto-Sign In</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <!-- Customer -->
                        <button type="button" class="btn btn-outline-primary btn-sm text-start p-2 d-flex align-items-center justify-content-between rounded-3" onclick="quickLogin('user@shopkart.in', 'password123')">
                            <div>
                                <span class="fw-bold d-block">👤 Customer: Pooja Verma</span>
                                <small class="text-muted">user@shopkart.in</small>
                            </div>
                            <span class="badge bg-primary text-white">Login &rarr;</span>
                        </button>

                        <!-- Seller -->
                        <button type="button" class="btn btn-outline-warning text-dark btn-sm text-start p-2 d-flex align-items-center justify-content-between rounded-3" onclick="quickLogin('lucknow.fashion@shopkart.in', 'password123')">
                            <div>
                                <span class="fw-bold d-block">🏪 Seller: Lucknow Fashion Hub</span>
                                <small class="text-muted">lucknow.fashion@shopkart.in</small>
                            </div>
                            <span class="badge bg-warning text-dark">Login &rarr;</span>
                        </button>

                        <!-- Admin -->
                        <button type="button" class="btn btn-outline-danger btn-sm text-start p-2 d-flex align-items-center justify-content-between rounded-3" onclick="quickLogin('admin@shopkart.in', 'password123')">
                            <div>
                                <span class="fw-bold d-block">🛡️ Platform Admin: Rajesh Sharma</span>
                                <small class="text-muted">admin@shopkart.in</small>
                            </div>
                            <span class="badge bg-danger text-white">Login &rarr;</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function quickLogin(email, pass) {
        document.getElementById('loginEmail').value = email;
        document.getElementById('loginPassword').value = pass;
        document.getElementById('loginForm').submit();
    }

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('loginPassword');
        const icon = document.getElementById('togglePasswordIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush
