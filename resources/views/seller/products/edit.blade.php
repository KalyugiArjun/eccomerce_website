@extends('layouts.seller')

@section('page_title', 'Edit Product: ' . $product->name)

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h5 class="fw-bold mb-0">Edit Product</h5>
        <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Products</a>
    </div>

    <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <label class="form-label small fw-bold">Product Title *</label>
                <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">SKU Code *</label>
                <input type="text" name="sku" class="form-control form-control-sm" value="{{ old('sku', $product->sku) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Category *</label>
                <select name="category_id" class="form-select form-select-sm" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Brand</label>
                <select name="brand_id" class="form-select form-select-sm">
                    <option value="">No Brand</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ $product->brand_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">GST Tax Percent (%)</label>
                <input type="number" name="tax_percent" class="form-control form-control-sm" value="{{ old('tax_percent', $product->tax_percent) }}" min="0" max="50">
            </div>

            <!-- Pricing & Inventory -->
            <div class="col-md-4">
                <label class="form-label small fw-bold">Regular Price (₹) *</label>
                <input type="number" name="price" class="form-control form-control-sm" value="{{ old('price', $product->price) }}" required min="1">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Discount Price (₹)</label>
                <input type="number" name="discount_price" class="form-control form-control-sm" value="{{ old('discount_price', $product->discount_price) }}" min="1">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Stock Quantity *</label>
                <input type="number" name="stock" class="form-control form-control-sm" value="{{ old('stock', $product->stock) }}" required min="0">
            </div>

            <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="is_fast_delivery" id="fastDelivCheck" value="1" {{ $product->is_fast_delivery ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold small" for="fastDelivCheck">Enable Fast Local Delivery</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Guaranteed Delivery Time (Minutes)</label>
                <input type="number" name="delivery_time_minutes" class="form-control form-control-sm" value="{{ old('delivery_time_minutes', $product->delivery_time_minutes) }}" min="10" max="180">
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Replace Thumbnail Image</label>
                <input type="file" name="thumbnail" class="form-control form-control-sm" accept="image/*">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Warranty Details</label>
                <input type="text" name="warranty" class="form-control form-control-sm" value="{{ old('warranty', $product->warranty) }}">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Short Summary</label>
                <input type="text" name="short_description" class="form-control form-control-sm" value="{{ old('short_description', $product->short_description) }}">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Full Product Description *</label>
                <textarea name="description" class="form-control form-control-sm" rows="4" required>{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-sk-primary px-4 py-2 fw-bold">
            Save Changes &rarr;
        </button>
    </form>
</div>
@endsection
