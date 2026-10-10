@extends('layouts.app')

@section('title', 'Fast Delivery Store (Under 30 Minutes) - ShopKart')

@section('content')
<div class="container">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm p-4 mb-4 bg-dark text-white rounded-4 overflow-hidden position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold mb-2">
                    <i class="bi bi-lightning-fill"></i> Instant Indian Fulfillment
                </span>
                <h2 class="display-6 fw-bold mb-2">Need it Fast? Get it in Minutes!</h2>
                <p class="text-light opacity-90 mb-0">Browse items stocked in verified local dark stores and neighborhood shops ready for instant doorstep courier dispatch.</p>
            </div>
            <div class="col-lg-4 text-end d-none d-lg-block">
                <i class="bi bi-clock-history text-warning" style="font-size: 5rem; opacity: 0.8;"></i>
            </div>
        </div>
    </div>

    <!-- Speed Tier Selection Bar (Section 9) -->
    <div class="card border-0 shadow-sm p-3 mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-bold small text-muted text-uppercase me-2">Delivery Time:</span>
            <a href="{{ route('fast-delivery.index', ['speed' => '30min']) }}" class="btn btn-sm {{ $speed === '30min' ? 'btn-warning fw-bold' : 'btn-outline-secondary' }}">
                <i class="bi bi-lightning-charge-fill"></i> Under 30 Minutes
            </a>
            <a href="{{ route('fast-delivery.index', ['speed' => '1hour']) }}" class="btn btn-sm {{ $speed === '1hour' ? 'btn-warning fw-bold' : 'btn-outline-secondary' }}">
                Under 1 Hour
            </a>
            <a href="{{ route('fast-delivery.index', ['speed' => 'today']) }}" class="btn btn-sm {{ $speed === 'today' ? 'btn-warning fw-bold' : 'btn-outline-secondary' }}">
                Today Guaranteed
            </a>
            <a href="{{ route('fast-delivery.index', ['speed' => 'tomorrow']) }}" class="btn btn-sm {{ $speed === 'tomorrow' ? 'btn-warning fw-bold' : 'btn-outline-secondary' }}">
                Tomorrow Morning
            </a>
            <a href="{{ route('fast-delivery.index', ['speed' => 'anytime']) }}" class="btn btn-sm {{ $speed === 'anytime' ? 'btn-warning fw-bold' : 'btn-outline-secondary' }}">
                Anytime
            </a>
        </div>
    </div>

    <!-- Products Grid -->
    @if($products->isEmpty())
        <div class="card border-0 shadow-sm p-5 text-center">
            <i class="bi bi-clock text-muted display-4 mb-3"></i>
            <h4>No products currently available for "{{ ucfirst($speed) }}" delivery in this area</h4>
            <p class="text-muted">Try selecting "Today Guaranteed" or "Anytime" to see more items.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
            @foreach($products as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
