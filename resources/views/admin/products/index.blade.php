@extends('layouts.admin')

@section('title', 'Manage Product Catalog - ShopKart Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Platform Product Catalog</h3>
        <p class="text-muted mb-0">Browse and moderate all vendor products across India.</p>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by product name, SKU..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Product Details</th>
                        <th>Seller / Store</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $product->main_image ?? 'https://placehold.co/80x80/f8f9fa/333?text=Product' }}" alt="{{ $product->name }}" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <div class="fw-semibold text-truncate" style="max-width: 250px;">{{ $product->name }}</div>
                                    <small class="text-muted font-monospace">SKU: {{ $product->sku }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $product->seller->store_name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $product->seller->city ?? '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $product->category->name ?? 'Uncategorized' }}</span>
                        </td>
                        <td>
                            <div class="fw-bold">₹{{ number_format($product->price, 2) }}</div>
                            @if($product->discount_price)
                                <small class="text-success">₹{{ number_format($product->discount_price, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($product->stock <= 5)
                                <span class="badge bg-danger-subtle text-danger">{{ $product->stock }} (Low)</span>
                            @else
                                <span class="badge bg-success-subtle text-success">{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.products.toggleFeatured', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $product->is_featured ? 'btn-warning text-dark' : 'btn-outline-secondary' }}" title="Toggle Featured status">
                                    <i class="bi bi-star{{ $product->is_featured ? '-fill' : '' }}"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('admin.products.updateStatus', $product->id) }}" method="POST">
                                @csrf
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="published" {{ $product->status == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="pending" {{ $product->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="draft" {{ $product->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="rejected" {{ $product->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </form>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View in Store">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                            No products found in the catalog.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
