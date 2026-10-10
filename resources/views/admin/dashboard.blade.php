@extends('layouts.admin')

@section('page_title', 'Platform Control Center')

@section('content')
<!-- KPI Cards (Section 18) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Platform Revenue</span>
                    <h3 class="fw-bold mb-0 text-success">₹{{ number_format($totalGrossRevenue) }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3"><i class="bi bi-cash-coin fs-3"></i></div>
            </div>
            <small class="text-muted mt-2 d-block">Gross platform GMV</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Platform Commission</span>
                    <h3 class="fw-bold mb-0 text-primary">₹{{ number_format($totalPlatformCommission) }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3"><i class="bi bi-pie-chart fs-3"></i></div>
            </div>
            <small class="text-muted mt-2 d-block">Net platform fees earned</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Sellers & Stores</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalSellers }}</h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3"><i class="bi bi-shop fs-3"></i></div>
            </div>
            <small class="text-danger fw-semibold mt-2 d-block">{{ $pendingSellers }} Pending Approvals</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Orders & Customers</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalOrders }}</h3>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-3"><i class="bi bi-bag-check fs-3"></i></div>
            </div>
            <small class="text-muted mt-2 d-block">{{ $totalUsers }} Registered Shoppers</small>
        </div>
    </div>
</div>

<!-- Charts Row (Section 18) -->
<div class="row g-4 mb-4">
    <!-- Revenue & Commission Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-3">Monthly Marketplace Revenue & Commission Trends</h6>
            <div style="height: 280px;">
                <canvas id="adminRevenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Sales Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-3">Products per Category</h6>
            <div style="height: 280px;">
                <canvas id="adminCategoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Pending Seller Approvals Table (Section 13) -->
<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-bold mb-0"><i class="bi bi-shield-exclamation text-warning me-2"></i> Pending Merchant Applications</h6>
            <small class="text-muted">Review credentials and approve merchant stores to allow product sales.</small>
        </div>
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm btn-link text-decoration-none">View All Sellers &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Store Name</th>
                    <th>City & State</th>
                    <th>Contact Phone</th>
                    <th>GSTIN / PAN</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingSellerList as $ps)
                    <tr>
                        <td><strong>{{ $ps->store_name }}</strong></td>
                        <td>{{ $ps->city }}, {{ $ps->state }}</td>
                        <td>{{ $ps->phone }}</td>
                        <td>{{ $ps->gst_number ?: $ps->pan_number }}</td>
                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                        <td class="text-end">
                            <form action="{{ route('admin.sellers.update-status', $ps->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-success btn-sm fw-bold">Approve</button>
                            </form>
                            <a href="{{ route('admin.sellers.show', $ps->id) }}" class="btn btn-outline-secondary btn-sm">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">No pending seller applications awaiting review.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Revenue & Commission Chart
        const revCtx = document.getElementById('adminRevenueChart').getContext('2d');
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: @json($monthLabels),
                datasets: [
                    {
                        label: 'Gross Sales (₹)',
                        data: @json($monthRevenue),
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Platform Commission (₹)',
                        data: @json($monthCommission),
                        borderColor: '#2563eb',
                        backgroundColor: 'transparent',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: { callback: function(val) { return '₹' + val; } }
                    }
                }
            }
        });

        // Category Pie Chart
        const catCtx = document.getElementById('adminCategoryChart').getContext('2d');
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_slice($catLabels, 0, 6)),
                datasets: [{
                    data: @json(array_slice($catProductCounts, 0, 6)),
                    backgroundColor: ['#f97316', '#2563eb', '#16a34a', '#8b5cf6', '#ec4899', '#eab308']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    });
</script>
@endpush
