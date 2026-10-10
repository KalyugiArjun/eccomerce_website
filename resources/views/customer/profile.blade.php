@extends('layouts.app')

@section('title', 'My Profile - ShopKart')

@section('content')
<div class="container py-3">
    <div class="row gy-4">
        <!-- Sidebar Navigation -->
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm p-4 text-center mb-3">
                <div class="bg-warning text-dark rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; font-size: 1.8rem; font-weight: bold;">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                <small class="text-muted">{{ $user->email }}</small>
                <div class="mt-2"><span class="badge bg-secondary text-uppercase">{{ $user->role }}</span></div>
            </div>

            <div class="list-group shadow-sm border-0">
                <a href="#profileTab" class="list-group-item list-group-item-action active fw-bold"><i class="bi bi-person me-2"></i> Profile Details</a>
                <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> My Orders</a>
                <a href="{{ route('wishlist.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-heart me-2"></i> My Wishlist</a>
            </div>
        </div>

        <!-- Main Profile Controls -->
        <div class="col-md-8 col-lg-9">
            <!-- 1. Profile Information -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-bold mb-3">Account Information</h5>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" class="form-control form-control-sm bg-light" value="{{ $user->email }}" disabled>
                            <small class="text-muted" style="font-size: 11px;">Email cannot be changed directly.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Number</label>
                            <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $user->phone) }}" required pattern="^[6-9]\d{9}$">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Default City</label>
                            <input type="text" name="preferred_city" class="form-control form-control-sm" value="{{ old('preferred_city', $user->preferred_city) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Default Pincode</label>
                            <input type="text" name="preferred_pincode" class="form-control form-control-sm" value="{{ old('preferred_pincode', $user->preferred_pincode) }}" pattern="^[1-9][0-9]{5}$">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sk-primary btn-sm mt-3 px-4">Update Profile</button>
                </form>
            </div>

            <!-- 2. Change Password -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-bold mb-3">Security & Password</h5>
                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="current_password" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="password" class="form-control form-control-sm" required minlength="8">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control form-control-sm" required minlength="8">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-dark btn-sm mt-3 px-4">Update Password</button>
                </form>
            </div>

            <!-- 3. Saved Addresses -->
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Saved Delivery Addresses</h5>
                @if($user->addresses->isEmpty())
                    <p class="text-muted small">No saved addresses yet. Addresses are saved automatically when you place an order.</p>
                @else
                    <div class="row g-3">
                        @foreach($user->addresses as $addr)
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light small">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong>{{ $addr->name }}</strong>
                                        <span class="badge bg-secondary">{{ ucfirst($addr->address_type) }}</span>
                                    </div>
                                    <div class="text-muted">{{ $addr->formatted_address }}</div>
                                    <div class="text-muted mt-1">Phone: {{ $addr->phone }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
