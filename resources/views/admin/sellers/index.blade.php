@extends('layouts.admin')

@section('title', 'Manage Sellers - ShopKart Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Seller Management & Verification</h3>
        <p class="text-muted mb-0">Approve onboarding requests, configure commissions, and monitor store compliance.</p>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.sellers.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by store name, city, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved (Active)</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Sellers Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Store & Seller</th>
                        <th>Location</th>
                        <th>Trust Score</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $seller)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 44px; height: 44px; font-weight: 700; color: var(--sk-primary);">
                                    {{ strtoupper(substr($seller->store_name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $seller->store_name }}</div>
                                    <small class="text-muted">{{ $seller->user->name ?? 'N/A' }} • {{ $seller->phone }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $seller->city }}, {{ $seller->state }}</div>
                            <small class="text-muted">{{ $seller->pincode }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-shield-check me-1"></i>{{ $seller->trust_score }} / 100
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $seller->commission_rate }}%</span>
                        </td>
                        <td>
                            @if($seller->status == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($seller->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($seller->status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-secondary">Suspended</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $seller->created_at->format('d M Y') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i> Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-shop fs-1 d-block mb-2 text-secondary"></i>
                            No sellers found matching the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sellers->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $sellers->links() }}
    </div>
    @endif
</div>
@endsection
