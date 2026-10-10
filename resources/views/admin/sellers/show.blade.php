@extends('layouts.admin')

@section('title', 'Review Seller: ' . $seller->store_name . ' - ShopKart Admin')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.sellers.index') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Back to Sellers List
    </a>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div>
            <h3 class="fw-bold mb-1">{{ $seller->store_name }}</h3>
            <span class="text-muted">Registered by {{ $seller->user->name ?? 'User' }} ({{ $seller->user->email ?? 'N/A' }}) on {{ $seller->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <div>
            @if($seller->status == 'approved')
                <span class="badge bg-success fs-6 px-3 py-2">Status: Approved</span>
            @elseif($seller->status == 'pending')
                <span class="badge bg-warning text-dark fs-6 px-3 py-2">Status: Pending Verification</span>
            @elseif($seller->status == 'rejected')
                <span class="badge bg-danger fs-6 px-3 py-2">Status: Rejected</span>
            @else
                <span class="badge bg-secondary fs-6 px-3 py-2">Status: Suspended</span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Store & Compliance Details -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Store & Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Store Name</label>
                        <div class="fw-semibold">{{ $seller->store_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Contact Phone</label>
                        <div class="fw-semibold">{{ $seller->phone }}</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Business Address</label>
                        <div class="fw-semibold">{{ $seller->address }}, {{ $seller->city }}, {{ $seller->state }} - {{ $seller->pincode }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Coordinates (Lat, Lng)</label>
                        <div class="fw-semibold">{{ $seller->latitude ?? 'N/A' }}, {{ $seller->longitude ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Delivery Radius</label>
                        <div class="fw-semibold">{{ $seller->delivery_radius_km }} km</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Est. Delivery Time</label>
                        <div class="fw-semibold">{{ $seller->estimated_delivery_time ?? '25-45 mins' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Store Description</label>
                        <div>{{ $seller->description ?? 'No description provided.' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance & Legal Information -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Statutory Tax & Banking Compliance</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">GSTIN Number</label>
                        <div class="fw-bold font-monospace text-primary">{{ $seller->gstin ?? 'Unregistered / Not Provided' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">PAN Number</label>
                        <div class="fw-bold font-monospace">{{ $seller->pan_number ?? 'Not Provided' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Bank Account Holder</label>
                        <div class="fw-semibold">{{ $seller->bank_account_name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Account Number</label>
                        <div class="fw-semibold font-monospace">{{ $seller->bank_account_number ? '•••• ' . substr($seller->bank_account_number, -4) : 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">IFSC Code</label>
                        <div class="fw-semibold font-monospace">{{ $seller->bank_ifsc ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seller Products -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Active Catalog Products ({{ $seller->products->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seller->products->take(10) as $p)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">{{ $p->name }}</div>
                                </td>
                                <td><code>{{ $p->sku }}</code></td>
                                <td class="fw-bold">₹{{ number_format($p->price, 2) }}</td>
                                <td>{{ $p->stock }}</td>
                                <td>
                                    <span class="badge bg-{{ $p->status == 'published' ? 'success' : 'secondary' }}">{{ $p->status }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">No products created yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions & Status Controls -->
    <div class="col-lg-4">
        <!-- Moderation Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="fw-bold mb-0 text-white">Status & Moderation</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sellers.updateStatus', $seller->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Seller Operational Status</label>
                        <select name="status" class="form-select" required>
                            <option value="approved" {{ $seller->status == 'approved' ? 'selected' : '' }}>Approved (Can sell & receive orders)</option>
                            <option value="pending" {{ $seller->status == 'pending' ? 'selected' : '' }}>Pending Review</option>
                            <option value="rejected" {{ $seller->status == 'rejected' ? 'selected' : '' }}>Reject Application</option>
                            <option value="suspended" {{ $seller->status == 'suspended' ? 'selected' : '' }}>Suspended (Store hidden)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Custom Platform Commission Rate (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" max="50" name="commission_rate" class="form-control" value="{{ old('commission_rate', $seller->commission_rate) }}">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Overrides default global commission for all orders sold by this vendor.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Update Seller Account
                    </button>
                </form>
            </div>
        </div>

        <!-- Trust Score & Operational Metrics -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Trust Score Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="text-center p-3 mb-3 bg-light rounded-3">
                    <div class="display-6 fw-bold text-success">{{ $seller->trust_score }} <span class="fs-6 text-muted">/ 100</span></div>
                    <div class="text-muted small">Algorithmic Trust Score</div>
                </div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Average Store Rating</span>
                        <span class="fw-bold text-warning"><i class="bi bi-star-fill me-1"></i>{{ number_format($seller->rating, 1) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Total Reviews</span>
                        <span class="fw-bold">{{ $seller->reviews_count ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Total Orders Fulfilled</span>
                        <span class="fw-bold">{{ $seller->sellerOrders->count() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Local Instant Delivery</span>
                        <span class="fw-bold text-{{ $seller->is_local_delivery_available ? 'success' : 'secondary' }}">
                            {{ $seller->is_local_delivery_available ? 'Enabled' : 'Disabled' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
