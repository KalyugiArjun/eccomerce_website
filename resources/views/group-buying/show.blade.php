@extends('layouts.app')

@section('title', 'Group Buy: ' . $group->product->name . ' - ShopKart')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <!-- Status & Expiry Header -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <span class="badge bg-danger text-uppercase px-2 py-1">Active Group Buy</span>
                        <h4 class="fw-bold mb-0 mt-2">Team Up to Unlock Wholesale Deal</h4>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Time Remaining:</small>
                        <div id="countdownTimer" class="fs-5 fw-bold text-danger font-monospace">Loading...</div>
                    </div>
                </div>

                <!-- Product Snapshot -->
                <div class="row align-items-center gy-3 mb-4 p-3 bg-light rounded-3">
                    <div class="col-sm-4 text-center">
                        <img src="{{ $group->product->thumbnail }}" class="img-fluid rounded" alt="{{ $group->product->name }}" style="max-height: 180px; object-fit: contain;">
                    </div>
                    <div class="col-sm-8">
                        <h5 class="fw-bold mb-2">
                            <a href="{{ route('products.show', $group->product->slug) }}" class="text-dark text-decoration-none">
                                {{ $group->product->name }}
                            </a>
                        </h5>
                        <div class="text-muted small mb-2">
                            Sold by <strong>{{ $group->product->seller->store_name }}</strong> &bull; Group Code: <code>{{ $group->group_code }}</code>
                        </div>

                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="display-6 fw-bold text-danger">₹{{ number_format($group->discounted_price) }}</span>
                            <span class="text-muted text-decoration-line-through fs-5">₹{{ number_format($group->original_price) }}</span>
                            <span class="badge bg-success ms-2">Save ₹{{ number_format($group->original_price - $group->discounted_price) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Participant Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between fw-bold mb-2">
                        <span>Participants Joined: {{ $group->current_buyers }} / {{ $group->target_buyers }}</span>
                        <span class="text-danger">{{ $group->remaining_buyers }} More Buyers Needed!</span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" style="width: {{ $group->progress_percentage }}%;"></div>
                    </div>
                </div>

                <!-- Action Controls: Join / Share -->
                <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
                    @if($userAlreadyJoined)
                        <div class="alert alert-success w-100 mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                            <div>
                                <strong>You have joined this group!</strong> Share the invite link below with friends or groups to complete the target faster.
                            </div>
                        </div>
                    @elseif($group->isFull())
                        <div class="alert alert-info w-100 mb-0">
                            <strong>This group buy has reached its target!</strong> All participants have unlocked this special pricing.
                        </div>
                    @else
                        <form action="{{ route('group-buying.join', $group->group_code) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 py-3 fw-bold fs-6">
                                <i class="bi bi-people-fill me-2"></i> Join This Group Deal (Pay ₹{{ number_format($group->discounted_price) }})
                            </button>
                        </form>
                    @endif

                    <button class="btn btn-outline-secondary px-4 py-2" onclick="navigator.clipboard.writeText(window.location.href); alert('Group Buy link copied to clipboard! Share it with friends on WhatsApp or Telegram.');">
                        <i class="bi bi-share-fill me-1"></i> Share Deal
                    </button>
                </div>

                <!-- Current Participants List -->
                <div class="pt-3 border-top">
                    <h6 class="fw-bold mb-3"><i class="bi bi-people text-secondary me-2"></i> Current Group Participants</h6>
                    <div class="row row-cols-1 row-cols-sm-2 g-2">
                        @foreach($group->participants as $p)
                            <div class="col">
                                <div class="p-2 border rounded bg-light d-flex align-items-center gap-2">
                                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 13px;">
                                        {{ substr($p->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong class="small d-block">{{ $p->user->name }}</strong>
                                        <small class="text-muted">Joined {{ $p->joined_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live Countdown Timer
    document.addEventListener("DOMContentLoaded", function () {
        const expiryTime = new Date("{{ $group->expires_at->toIso8601String() }}").getTime();

        const timer = setInterval(function () {
            const now = new Date().getTime();
            const distance = expiryTime - now;

            if (distance < 0) {
                clearInterval(timer);
                document.getElementById("countdownTimer").innerText = "EXPIRED";
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdownTimer").innerText = 
                (hours < 10 ? "0" + hours : hours) + "h : " +
                (minutes < 10 ? "0" + minutes : minutes) + "m : " +
                (seconds < 10 ? "0" + seconds : seconds) + "s";
        }, 1000);
    });
</script>
@endpush
