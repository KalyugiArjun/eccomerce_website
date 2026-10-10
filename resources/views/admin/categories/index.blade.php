@extends('layouts.admin')

@section('title', 'Categories & Brands - ShopKart Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Categories & Brand Catalog</h3>
        <p class="text-muted mb-0">Organize marketplace taxonomy and configure default category-level commission fees.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Categories List & Create -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Marketplace Categories</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Category
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Category Name</th>
                                <th>Subcategories</th>
                                <th>Default Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-2 bg-light rounded text-primary">
                                            <i class="bi {{ $category->icon ?? 'bi-tag' }}"></i>
                                        </div>
                                        <div class="fw-semibold">{{ $category->name }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($category->subcategories as $sub)
                                            <span class="badge bg-light text-dark border">{{ $sub->name }}</span>
                                        @empty
                                            <span class="text-muted small">None</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $category->commission_rate }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No categories configured.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Brands List & Create -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Verified Brands</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createBrandModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Brand
                </button>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($brands as $brand)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-semibold">{{ $brand->name }}</div>
                            <small class="text-muted">{{ Str::limit($brand->description, 50) }}</small>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $brand->products_count }} products</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center py-4 text-muted">No brands found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Category -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Marketplace Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Footwear & Shoes">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Parent Category (Optional)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top Level Root Category)</option>
                            @foreach($categories as $pc)
                                <option value="{{ $pc->id }}">{{ $pc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bootstrap Icon Class</label>
                        <input type="text" name="icon" class="form-control" placeholder="e.g. bi-laptop, bi-phone, bi-watch" value="bi-tag">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Default Platform Commission (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="commission_rate" class="form-control" value="10.0" min="0" max="50">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Brand -->
<div class="modal fade" id="createBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.brands.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Verified Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Brand Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Nike, Boat, Samsung">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brand story and notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Add Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
