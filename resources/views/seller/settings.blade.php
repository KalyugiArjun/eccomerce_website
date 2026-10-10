@extends('layouts.seller')

@section('page_title', 'Store Profile & Delivery Settings')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <form action="{{ route('seller.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <h5 class="fw-bold mb-3"><i class="bi bi-shop text-primary me-2"></i> Storefront Identity</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Store Name *</label>
                <input type="text" name="store_name" class="form-control form-control-sm" value="{{ old('store_name', $seller->store_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Store Tagline</label>
                <input type="text" name="store_tagline" class="form-control form-control-sm" value="{{ old('store_tagline', $seller->store_tagline) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Contact Phone Number *</label>
                <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $seller->phone) }}" required pattern="^[6-9]\d{9}$">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Business Email *</label>
                <input type="email" name="business_email" class="form-control form-control-sm" value="{{ old('business_email', $seller->business_email) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">About Your Store</label>
                <textarea name="about_store" class="form-control form-control-sm" rows="3">{{ old('about_store', $seller->about_store) }}</textarea>
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt text-danger me-2"></i> Local Delivery Configuration</h5>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label small fw-bold">Physical Business Address *</label>
                <input type="text" name="business_address" class="form-control form-control-sm" value="{{ old('business_address', $seller->business_address) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">City *</label>
                <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $seller->city) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Pincode *</label>
                <input type="text" name="pincode" class="form-control form-control-sm" value="{{ old('pincode', $seller->pincode) }}" required pattern="^[1-9][0-9]{5}$">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Delivery Radius (km) *</label>
                <input type="number" name="delivery_radius_km" class="form-control form-control-sm" value="{{ old('delivery_radius_km', $seller->delivery_radius_km) }}" min="1" max="100" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Average Local Delivery Speed (Minutes) *</label>
                <input type="number" name="estimated_delivery_minutes" class="form-control form-control-sm" value="{{ old('estimated_delivery_minutes', $seller->estimated_delivery_minutes) }}" min="10" max="300" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Store Return & Replacement Policy</label>
                <input type="text" name="return_policy" class="form-control form-control-sm" value="{{ old('return_policy', $seller->return_policy) }}">
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-image text-warning me-2"></i> Storefront Branding Images</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Upload New Store Logo</label>
                <input type="file" name="store_logo" class="form-control form-control-sm" accept="image/*">
                @if($seller->store_logo)
                    <div class="mt-2"><img src="{{ $seller->store_logo }}" style="width: 50px; height: 50px; object-fit: cover;" class="rounded border"></div>
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Upload New Banner Image</label>
                <input type="file" name="store_banner" class="form-control form-control-sm" accept="image/*">
            </div>
        </div>

        <button type="submit" class="btn btn-sk-primary px-4 py-2 fw-bold">
            Save Store Configuration &rarr;
        </button>
    </form>
</div>
@endsection
