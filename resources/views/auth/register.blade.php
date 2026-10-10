@extends('layouts.app')

@section('title', 'Register as Customer - ShopKart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card border-0 shadow rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="sk-brand mb-2">
                        <span>Shop</span><span class="sk-brand-badge">Kart</span>
                    </div>
                    <h3 class="fw-bold mb-1">Create Customer Account</h3>
                    <p class="text-muted small">Join India's multi-vendor marketplace with 30-min local delivery & transparent prices</p>
                </div>

                {{-- Validation Error Alerts --}}
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                        <div class="small">
                            <strong>Registration Incomplete:</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST" id="registerForm">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" id="regName" class="form-control border-start-0 ps-0" value="{{ old('name') }}" required autofocus placeholder="e.g. Rahul Sharma">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="regEmail" class="form-control border-start-0 ps-0" value="{{ old('email') }}" required placeholder="name@example.com">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Indian Mobile (10 Digits) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-semibold text-muted">+91</span>
                                <input type="tel" name="phone" id="regPhone" class="form-control border-start-0 ps-1" value="{{ old('phone') }}" required pattern="^[6-9]\d{9}$" maxlength="10" placeholder="9876543210">
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Enter 10-digit mobile starting with 6, 7, 8 or 9</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">City</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="preferred_city" id="regCity" class="form-control border-start-0 ps-0" value="{{ old('preferred_city', 'Lucknow') }}" placeholder="e.g. Lucknow, Delhi">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Pincode (6 Digits)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-pin-map"></i></span>
                                <input type="text" name="preferred_pincode" id="regPincode" class="form-control border-start-0 ps-0" value="{{ old('preferred_pincode', '226001') }}" pattern="^[1-9][0-9]{5}$" maxlength="6" placeholder="e.g. 226001">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="regPassword" class="form-control border-start-0 ps-0" required minlength="8" placeholder="Min 8 characters">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                                <input type="password" name="password_confirmation" id="regPasswordConf" class="form-control border-start-0 ps-0" required minlength="8" placeholder="Re-enter password">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-sk-primary w-100 py-2 fw-bold fs-6 shadow-sm">
                            <i class="bi bi-person-check-fill me-1"></i> Create Customer Account &rarr;
                        </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 small">
                        <button type="button" class="btn btn-link text-decoration-none btn-sm p-0 text-muted" onclick="fillSampleData()">
                            <i class="bi bi-magic me-1"></i> Fill Sample Details
                        </button>
                        <div>
                            Already registered? <a href="{{ route('login') }}" class="text-warning fw-bold text-decoration-none">Sign In</a>
                        </div>
                    </div>
                </form>

                <!-- Seller Registration Banner -->
                <div class="mt-4 pt-3 border-top">
                    <div class="p-3 bg-warning-subtle rounded-3 border border-warning-subtle d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-bold text-dark"><i class="bi bi-shop text-warning me-1"></i> Want to sell on ShopKart?</div>
                            <small class="text-muted">Register your store, reach nearby buyers, and get fast payouts.</small>
                        </div>
                        <a href="{{ route('seller.register') }}" class="btn btn-warning btn-sm fw-bold px-3">
                            Become a Seller &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function fillSampleData() {
        const rand = Math.floor(Math.random() * 9000) + 1000;
        document.getElementById('regName').value = 'Rohan Gupta';
        document.getElementById('regEmail').value = 'rohan' + rand + '@example.com';
        document.getElementById('regPhone').value = '98' + Math.floor(10000000 + Math.random() * 90000000);
        document.getElementById('regCity').value = 'Lucknow';
        document.getElementById('regPincode').value = '226001';
        document.getElementById('regPassword').value = 'password123';
        document.getElementById('regPasswordConf').value = 'password123';
    }
</script>
@endpush
