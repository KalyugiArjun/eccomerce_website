@extends('layouts.seller')

@section('page_title', 'Product Catalog Management')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <form action="{{ route('seller.products.index') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or SKU..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-dark btn-sm">Search</button>
        </form>

        <a href="{{ route('seller.products.create') }}" class="btn btn-sk-primary btn-sm fw-bold">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $p->thumbnail ?: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=100&h=100&fit=crop' }}" class="rounded" style="width: 45px; height: 45px; object-fit: cover;">
                                <div>
                                    <strong class="d-block text-dark">{{ Str::limit($p->name, 35) }}</strong>
                                    <small class="text-muted">SKU: <code>{{ $p->sku }}</code></small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $p->category->name }}</td>
                        <td>
                            <strong>₹{{ number_format($p->effective_price) }}</strong>
                            @if($p->discount_price)
                                <small class="text-muted text-decoration-line-through d-block">₹{{ number_format($p->price) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($p->stock <= 5)
                                <span class="badge bg-danger">{{ $p->stock }} left (Low)</span>
                            @else
                                <span class="badge bg-success">{{ $p->stock }} units</span>
                            @endif
                        </td>
                        <td>
                            @if($p->is_fast_delivery)
                                <span class="badge bg-warning text-dark"><i class="bi bi-lightning-fill"></i> {{ $p->delivery_time_minutes }}m</span>
                            @else
                                <span class="text-muted small">Standard</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success text-capitalize">{{ $p->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('seller.products.edit', $p->id) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('seller.products.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No products listed in your store yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
