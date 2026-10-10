@extends('layouts.app')

@section('title', $wishlist->name . ' - Shared Wishlist on ShopKart')

@section('content')
<div class="container py-4">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm p-4 mb-4 text-center bg-light">
        <div class="mb-2 text-danger"><i class="bi bi-gift-fill display-4"></i></div>
        <h2 class="fw-bold mb-1">{{ $wishlist->name }}</h2>
        @if($wishlist->event_name)
            <span class="badge bg-danger text-uppercase px-3 py-1 mb-2 d-inline-block">{{ $wishlist->event_name }}</span>
        @endif
        <p class="text-muted small mb-0">Check out these wishlist products selected on ShopKart. You can gift or purchase them directly!</p>
    </div>

    <!-- Products in Wishlist -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-5">
        @foreach($wishlist->items as $item)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm p-3 d-flex flex-column">
                    <img src="{{ $item->product->thumbnail }}" class="card-img-top rounded mb-2" alt="{{ $item->product->name }}" style="height: 160px; object-fit: cover;">
                    <h6 class="fw-bold mb-1 fs-6">
                        <a href="{{ route('products.show', $item->product->slug) }}" class="text-dark text-decoration-none">
                            {{ Str::limit($item->product->name, 40) }}
                        </a>
                    </h6>
                    <small class="text-muted mb-2">Sold by {{ $item->product->seller->store_name }}</small>
                    <div class="fw-bold text-dark fs-5 mb-3 mt-auto">₹{{ number_format($item->product->effective_price) }}</div>

                    <a href="{{ route('products.show', $item->product->slug) }}" class="btn btn-sk-primary btn-sm w-100 fw-semibold">
                        <i class="bi bi-gift me-1"></i> Buy as Gift
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
