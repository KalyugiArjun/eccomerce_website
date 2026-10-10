@extends('layouts.app')

@section('title', 'Visual Search Results - ShopKart')

@section('content')
<div class="container py-3">
    <!-- Analysis Breakdown -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="row align-items-center gy-3">
            <div class="col-md-3 text-center">
                <img src="{{ $uploadedImage }}" alt="Uploaded Image" class="img-thumbnail" style="max-height: 140px; object-fit: cover;">
                <div class="small text-muted mt-1">Uploaded Image</div>
            </div>
            <div class="col-md-9">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success"><i class="bi bi-cpu"></i> Visual Detection Complete</span>
                    <span class="badge bg-light text-dark border">{{ $confidence }}% Match Confidence</span>
                </div>
                <h4 class="fw-bold mb-2">Visual Characteristics Detected</h4>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @foreach($detected['tags'] as $tag)
                        <span class="badge bg-secondary text-uppercase">{{ $tag }}</span>
                    @endforeach
                    <span class="badge bg-dark">Color: {{ $detected['detected_color'] }}</span>
                    <span class="badge bg-info text-dark">Style: {{ $detected['visual_pattern'] }}</span>
                </div>
                <small class="text-muted">Showing genuine catalog items matching identified features.</small>
            </div>
        </div>
    </div>

    <!-- Similar Products Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Similar Products Found</h4>
        <a href="{{ route('visual.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-camera me-1"></i> Scan Another Image
        </a>
    </div>

    @if($products->isEmpty())
        <div class="card border-0 shadow-sm p-5 text-center">
            <p class="text-muted">No similar items found in catalog. Try another angle or clearer image.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-5">
            @foreach($products as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
