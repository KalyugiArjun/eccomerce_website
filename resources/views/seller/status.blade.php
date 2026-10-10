@extends('layouts.app')

@section('title', 'Seller Application Status - ShopKart')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm p-4 text-center">
                @if($seller->status === 'approved')
                    <div class="mb-3 text-success">
                        <i class="bi bi-patch-check-fill display-3"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Store Approved & Active!</h3>
                    <p class="text-muted">Congratulations! Your seller store <strong>{{ $seller->store_name }}</strong> has been verified and approved by the ShopKart administration team.</p>
                    <div class="mt-4">
                        <a href="{{ route('seller.dashboard') }}" class="btn btn-sk-primary px-4 py-2 fw-bold">
                            Go to Seller Dashboard &rarr;
                        </a>
                    </div>
                @elseif($seller->status === 'pending')
                    <div class="mb-3 text-warning">
                        <i class="bi bi-clock-history display-3"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Application Under Review</h3>
                    <p class="text-muted">Thank you for submitting your merchant application for <strong>{{ $seller->store_name }}</strong>. Our compliance team is verifying your business address, PAN, and credentials.</p>
                    <div class="alert alert-light border small text-muted text-start my-3">
                        <div><strong>Store Name:</strong> {{ $seller->store_name }}</div>
                        <div><strong>Address:</strong> {{ $seller->business_address }}, {{ $seller->city }}</div>
                        <div><strong>Status:</strong> <span class="badge bg-warning text-dark">PENDING ADMIN APPROVAL</span></div>
                    </div>
                    <p class="small text-muted mb-0">Merchant accounts are typically approved within 24 hours. You will receive an email once your storefront is live.</p>
                @elseif($seller->status === 'suspended')
                    <div class="mb-3 text-danger">
                        <i class="bi bi-slash-circle-fill display-3"></i>
                    </div>
                    <h3 class="fw-bold mb-2 text-danger">Store Suspended</h3>
                    <p class="text-muted">Your store account has been suspended due to compliance or customer service review. Please contact seller support.</p>
                @else
                    <div class="mb-3 text-danger">
                        <i class="bi bi-x-circle-fill display-3"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Application Not Approved</h3>
                    <p class="text-muted">Unfortunately, your application could not be approved at this time. Please ensure correct business documents are provided.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
