<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product', 'items.seller', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty. Please add items to checkout.');
        }

        $addresses = $user->addresses()->latest()->get();
        $itemsBySeller = $cart->itemsGroupedBySeller();

        // Calculate totals
        $couponCode = session('applied_coupon');
        $couponDiscount = 0.0;
        $appliedCoupon = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon) {
                $validation = $coupon->isValidForAmount($cart->subtotal, $user->id);
                if ($validation['valid']) {
                    $couponDiscount = $validation['discount'];
                    $appliedCoupon = $coupon;
                }
            }
        }

        $grandTotal = max(0, round($cart->subtotal + $cart->tax_amount + $cart->shipping_fee - $couponDiscount, 2));

        return view('checkout.index', compact(
            'cart',
            'addresses',
            'itemsBySeller',
            'appliedCoupon',
            'couponDiscount',
            'grandTotal'
        ));
    }

    public function process(CheckoutRequest $request)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product', 'items.seller', 'items.variant'])
            ->firstOrFail();

        // 1. Resolve or create shipping address
        if ($request->filled('address_id')) {
            $address = Address::where('id', $request->address_id)->where('user_id', $user->id)->firstOrFail();
            $shippingAddressArray = [
                'name' => $address->name,
                'phone' => $address->phone,
                'alternate_phone' => $address->alternate_phone,
                'house_no' => $address->house_no,
                'street' => $address->street,
                'area' => $address->area,
                'landmark' => $address->landmark,
                'city' => $address->city,
                'district' => $address->district,
                'state' => $address->state,
                'pincode' => $address->pincode,
                'formatted' => $address->formatted_address,
            ];
        } else {
            $newAddress = Address::create([
                'user_id' => $user->id,
                'name' => $request->customer_name,
                'phone' => $request->customer_phone,
                'house_no' => $request->house_no,
                'street' => $request->street,
                'area' => $request->area,
                'landmark' => $request->landmark,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'is_default' => $user->addresses()->count() === 0,
            ]);

            $shippingAddressArray = [
                'name' => $newAddress->name,
                'phone' => $newAddress->phone,
                'house_no' => $newAddress->house_no,
                'street' => $newAddress->street,
                'area' => $newAddress->area,
                'landmark' => $newAddress->landmark,
                'city' => $newAddress->city,
                'state' => $newAddress->state,
                'pincode' => $newAddress->pincode,
                'formatted' => $newAddress->formatted_address,
            ];
        }

        $orderData = [
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'shipping_address' => $shippingAddressArray,
            'payment_method' => $request->payment_method,
            'coupon_code' => session('applied_coupon'),
            'notes' => $request->notes,
            // Gift Mode Features
            'is_gift' => $request->boolean('is_gift'),
            'gift_recipient_name' => $request->gift_recipient_name,
            'gift_message' => $request->gift_message,
            'gift_wrap' => $request->boolean('gift_wrap'),
            'hide_price' => $request->boolean('hide_price'),
            'scheduled_delivery_date' => $request->scheduled_delivery_date,
        ];

        try {
            // Create main order and split into seller sub-orders
            $order = $this->orderService->createOrder($user, $orderData, $cart);

            // Process payment
            $payment = $this->paymentService->processPayment($order, $request->payment_method, $request->all());

            // Clear session coupon
            session()->forget('applied_coupon');

            return redirect()->route('checkout.success', $order->order_number)
                ->with('success', "Order #{$order->order_number} successfully placed!");

        } catch (Exception $e) {
            return back()->with('error', 'Checkout error: ' . $e->getMessage())->withInput();
        }
    }

    public function success(string $orderNumber)
    {
        $order = Order::with(['sellerOrders.items', 'sellerOrders.seller', 'sellerOrders.shipment', 'payment'])
            ->where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}
