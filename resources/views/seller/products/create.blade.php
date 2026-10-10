@extends('layouts.seller')

@section('page_title', 'Add New Product')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h5 class="fw-bold mb-0">Create Product Listing</h5>
        <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Products</a>
    </div>

    <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <label class="form-label small fw-bold">Product Title *</label>
                <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name') }}" required placeholder="e.g. boAt Rockerz 450 Bluetooth Wireless On Ear Headphones">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">SKU Code *</label>
                <input type="text" name="sku" class="form-control form-control-sm" value="{{ old('sku', 'SKU-' . rand(10000, 99999)) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Category *</label>
                <select name="category_id" class="form-select form-select-sm" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Brand (Optional)</label>
                <select name="brand_id" class="form-select form-select-sm">
                    <option value="">No Brand / Generic</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">GST Tax Percent (%)</label>
                <input type="number" name="tax_percent" class="form-control form-control-sm" value="18" min="0" max="50">
            </div>

            <!-- Pricing & Inventory -->
            <div class="col-md-4">
                <label class="form-label small fw-bold">MRP / Regular Price (₹) *</label>
                <input type="number" name="price" class="form-control form-control-sm" value="{{ old('price') }}" required min="1" placeholder="₹">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Offer / Discount Price (₹)</label>
                <input type="number" name="discount_price" class="form-control form-control-sm" value="{{ old('discount_price') }}" min="1" placeholder="₹">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Stock Quantity *</label>
                <input type="number" name="stock" class="form-control form-control-sm" value="{{ old('stock', 20) }}" required min="0">
            </div>

            <!-- Fast Delivery & Highlights -->
            <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="is_fast_delivery" id="fastDelivCheck" value="1" checked>
                    <label class="form-check-label fw-bold small" for="fastDelivCheck">Enable Fast Local Delivery</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Guaranteed Delivery Time (Minutes)</label>
                <input type="number" name="delivery_time_minutes" class="form-control form-control-sm" value="30" min="10" max="180">
            </div>

            <!-- Images & Descriptions -->
            <div class="col-md-6">
                <label class="form-label small fw-bold">Main Product Image (Thumbnail)</label>
                <input type="file" name="thumbnail" class="form-control form-control-sm" accept="image/*">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Warranty Details</label>
                <input type="text" name="warranty" class="form-control form-control-sm" value="1 Year Manufacturer Warranty">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Short Summary</label>
                <input type="text" name="short_description" class="form-control form-control-sm" placeholder="Brief 1-line product feature highlight">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Full Product Description *</label>
                <textarea name="description" class="form-control form-control-sm" rows="4" required placeholder="Detailed specifications, material, care instructions, compatibility..."></textarea>
            </div>
        </div>

        <!-- Variants Section (Section 5) -->
        <h6 class="fw-bold mb-2 pt-3 border-top"><i class="bi bi-collection text-primary me-2"></i> Product Variants (Optional)</h6>
        <p class="text-muted small">You can define color and size variants with individual pricing and stock.</p>

        <div id="variantsContainer" class="mb-3">
            <div class="row g-2 mb-2 p-2 bg-light rounded border align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">SKU</label>
                    <input type="text" name="variants[0][sku]" class="form-control form-control-sm" placeholder="SKU-VAR-1">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Color</label>
                    <input type="text" name="variants[0][color]" class="form-control form-control-sm" placeholder="e.g. Black">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Size</label>
                    <input type="text" name="variants[0][size]" class="form-control form-control-sm" placeholder="e.g. M / UK 8">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Price (₹)</label>
                    <input type="number" name="variants[0][price]" class="form-control form-control-sm" placeholder="₹">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Stock</label>
                    <input type="number" name="variants[0][stock]" class="form-control form-control-sm" value="10">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-sk-primary px-4 py-2 fw-bold">
            Publish Product Listing &rarr;
        </button>
    </form>
</div>
@endsection
