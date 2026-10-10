@extends('layouts.seller')

@section('page_title', 'Seller Dashboard - ' . $seller->store_name)

@section('content')
<div class="row g-3 mb-4">
    <!-- 1. Gross Sales -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Gross Sales</span>
                    <h3 class="fw-bold mb-0 text-dark">₹{{ number_format($grossSales) }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3"><i class="bi bi-currency-rupee fs-3"></i></div>
            </div>
            <small class="text-muted mt-2 d-block">Across all customer orders</small>
        </div>
    </div>

    <!-- 2. Net Earnings -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Net Earnings</span>
                    <h3 class="fw-bold mb-0 text-success">₹{{ number_format($netEarnings) }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3"><i class="bi bi-wallet2 fs-3"></i></div>
            </div>
            <small class="text-muted mt-2 d-block">After platform commission</small>
        </div>
    </div>

    <!-- 3. Total Orders -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Sub-Orders</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalOrders }}</h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3"><i class="bi bi-box-seam fs-3"></i></div>
            </div>
            <small class="text-success mt-2 d-block">{{ $completedOrders }} Delivered &bull; {{ $pendingOrders }} In Progress</small>
        </div>
    </div>

    <!-- 4. Trust Score -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Store Trust Score</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $seller->trust_score }}%</h3>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-3"><i class="bi bi-shield-check fs-3"></i></div>
            </div>
            <small class="text-muted mt-2 d-block">Rating: {{ $seller->rating }} ★ ({{ $seller->total_reviews }} reviews)</small>
        </div>
    </div>
</div>

<!-- Sales Trend Chart & Quick Alerts -->
<div class="row g-4 mb-4">
    <!-- Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Sales Trend (Last 7 Days)</h6>
                <span class="badge bg-light text-dark border">INR (₹)</span>
            </div>
            <div style="height: 280px;">
                <canvas id="sellerSalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Action Alerts -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-3">Store Health & Action Items</h6>
            <div class="d-flex flex-column gap-3">
                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Active Catalog Products</div>
                        <small class="text-muted">{{ $totalProducts }} items live</small>
                    </div>
                    <a href="{{ route('seller.products.create') }}" class="btn btn-sm btn-dark">+ Add New</a>
                </div>

                <div class="p-3 bg-warning bg-opacity-10 border border-warning rounded-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">Low Stock Alerts</div>
                        <small class="text-muted">{{ $lowStockCount }} items under 5 units</small>
                    </div>
                    <a href="{{ route('seller.inventory.index', ['low_stock' => 1]) }}" class="btn btn-sm btn-warning fw-bold">Restock</a>
                </div>

                <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-danger">Pending Return Claims</div>
                        <small class="text-muted">{{ $refundRequests }} requests awaiting response</small>
                    </div>
                    <span class="badge bg-danger">{{ $refundRequests }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sub-Orders Table -->
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Recent Customer Sub-Orders</h6>
        <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-link text-decoration-none">View All Orders &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Sub-Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Subtotal (₹)</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $so)
                    <tr>
                        <td><code>{{ $so->sub_order_number }}</code></td>
                        <td>{{ $so->order->customer_name }}</td>
                        <td>{{ $so->items->count() }} items</td>
                        <td class="fw-bold">₹{{ number_format($so->grand_total) }}</td>
                        <td><span class="badge {{ $so->status_badge_class }}">{{ ucfirst($so->status) }}</span></td>
                        <td class="small text-muted">{{ $so->created_at->format('d M, h:i A') }}</td>
                        <td class="text-end">
                            <a href="{{ route('seller.orders.show', $so->sub_order_number) }}" class="btn btn-sm btn-outline-primary">
                                Manage Dispatch
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No orders received yet.</td>
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
        const ctx = document.getElementById('sellerSalesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Daily Sales (₹)',
                    data: @json($chartSales),
                    backgroundColor: '#f97316',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            callback: function(val) { return '₹' + val; }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
