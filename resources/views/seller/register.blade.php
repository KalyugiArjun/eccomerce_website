@extends('layouts.app')

@section('title', 'Register as a Seller - ShopKart')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <div class="text-center mb-4 pb-3 border-bottom">
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold mb-2">ShopKart Merchant Network</span>
                    <h3 class="fw-bold mb-1">Sell on ShopKart & Reach Local Shoppers</h3>
                    <p class="text-muted small mb-0">Join thousands of verified local Indian stores offering fast delivery, smart price comparison and direct store sales.</p>
                </div>

                <form action="{{ route('seller.register.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Store Details -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-shop text-primary me-2"></i> 1. Store Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Store / Business Name *</label>
                            <input type="text" name="store_name" class="form-control form-control-sm" value="{{ old('store_name') }}" required placeholder="e.g. Lucknow SportsHub">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Store Tagline</label>
                            <input type="text" name="store_tagline" class="form-control form-control-sm" value="{{ old('store_tagline') }}" placeholder="e.g. Premium Sports Gear & Fast Delivery">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Business Contact Mobile *</label>
                            <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', Auth::user()?->phone) }}" required pattern="^[6-9]\d{9}$">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Business Email *</label>
                            <input type="email" name="business_email" class="form-control form-control-sm" value="{{ old('business_email', Auth::user()?->email) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Store Description / About</label>
                            <textarea name="about_store" class="form-control form-control-sm" rows="2" placeholder="Tell customers about your products and legacy..."></textarea>
                        </div>
                    </div>

                    <!-- Business Address & Delivery Radius (Section 8 & 9) -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt text-danger me-2"></i> 2. Location & Delivery Radius</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Full Physical Store Address *</label>
                            <input type="text" name="business_address" class="form-control form-control-sm" required placeholder="Shop No., Market / Complex, Street">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">City *</label>
                            <input type="text" name="city" class="form-control form-control-sm" value="Lucknow" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">State *</label>
                            <input type="text" name="state" class="form-control form-control-sm" value="Uttar Pradesh" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Pincode *</label>
                            <input type="text" name="pincode" class="form-control form-control-sm" value="226001" required pattern="^[1-9][0-9]{5}$">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Local Delivery Radius (in Kilometers)</label>
                            <input type="number" name="delivery_radius_km" class="form-control form-control-sm" value="15" min="1" max="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Estimated Local Delivery Time (Minutes)</label>
                            <input type="number" name="estimated_delivery_minutes" class="form-control form-control-sm" value="30" min="10" max="300">
                        </div>
                    </div>

                    <!-- Tax & Banking Details (Section 13 & 43) -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-bank text-success me-2"></i> 3. Tax & Bank Account Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">PAN Card Number</label>
                            <input type="text" name="pan_number" class="form-control form-control-sm text-uppercase" placeholder="e.g. ABCDE1234F" pattern="^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">GSTIN (Optional)</label>
                            <input type="text" name="gst_number" class="form-control form-control-sm text-uppercase" placeholder="15-character GSTIN">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="e.g. HDFC Bank">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Account Holder Name</label>
                            <input type="text" name="bank_account_name" class="form-control form-control-sm" placeholder="e.g. Lucknow SportsHub">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Bank Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control form-control-sm" placeholder="Account Number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Bank IFSC Code</label>
                            <input type="text" name="bank_ifsc" class="form-control form-control-sm text-uppercase" placeholder="e.g. HDFC0001234" pattern="^[A-Za-z]{4}0[A-Za-z0-9]{6}$">
                        </div>
                    </div>

                    <!-- Store Logo & Docs -->
                    <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-check text-warning me-2"></i> 4. Store Branding</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Store Logo (Image)</label>
                            <input type="file" name="store_logo" class="form-control form-control-sm" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sk-primary w-100 py-3 fw-bold">
                        Submit Merchant Application &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
