@extends('layouts.seller')

@section('page_title', 'Inventory & Stock Management')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-bold mb-0">Warehouse & Dark Store Inventory</h6>
            <small class="text-muted">Keep stock updated to prevent stockouts and preserve your Seller Trust Score.</small>
        </div>
        <div>
            @if(request('low_stock'))
                <a href="{{ route('seller.inventory.index') }}" class="btn btn-outline-secondary btn-sm">Show All Stock</a>
            @else
                <a href="{{ route('seller.inventory.index', ['low_stock' => 1]) }}" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-exclamation-triangle"></i> Show Low Stock Only
                </a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Variant</th>
                    <th>Available Stock</th>
                    <th>Low Threshold</th>
                    <th>Status</th>
                    <th class="text-end">Quick Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventories as $inv)
                    <tr>
                        <td>
                            <strong>{{ $inv->product->name }}</strong>
                        </td>
                        <td><code>{{ $inv->sku }}</code></td>
                        <td>{{ $inv->variant ? ($inv->variant->color . ' - ' . $inv->variant->size) : 'Base' }}</td>
                        <td>
                            <span class="fs-6 fw-bold text-dark">{{ $inv->stock_quantity }}</span>
                        </td>
                        <td>{{ $inv->low_stock_threshold }} units</td>
                        <td>
                            @if($inv->isLowStock())
                                <span class="badge bg-danger">Low Stock Warning</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('seller.inventory.update', $inv->id) }}" method="POST" class="d-inline-flex gap-1 align-items-center justify-content-end">
                                @csrf
                                <input type="number" name="stock_quantity" class="form-control form-control-sm text-center" style="width: 75px;" value="{{ $inv->stock_quantity }}" min="0">
                                <input type="hidden" name="low_stock_threshold" value="{{ $inv->low_stock_threshold }}">
                                <button type="submit" class="btn btn-dark btn-sm">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No inventory records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $inventories->links() }}
    </div>
</div>
@endsection
