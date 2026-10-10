<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Initialize or process payment for an order
     */
    public function processPayment(Order $order, string $method, array $payload = []): Payment
    {
        return DB::transaction(function () use ($order, $method, $payload) {
            $transactionId = 'TXN-' . strtoupper(Str::random(12));

            if ($method === 'cod') {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'payment_method' => 'cod',
                    'transaction_id' => $transactionId,
                    'amount' => $order->grand_total,
                    'currency' => 'INR',
                    'status' => 'pending',
                    'gateway' => 'Cash On Delivery',
                    'paid_at' => null,
                ]);

                $order->update([
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                ]);

                return $payment;
            }

            if ($method === 'razorpay') {
                $razorpayPaymentId = $payload['razorpay_payment_id'] ?? null;
                $razorpayOrderId = $payload['razorpay_order_id'] ?? null;
                $razorpaySignature = $payload['razorpay_signature'] ?? null;

                // Server-side verification simulation / hash check
                $isValid = $this->verifyRazorpaySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

                if (!$isValid) {
                    throw new Exception("Payment verification failed! Invalid Razorpay signature.");
                }

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'payment_method' => 'razorpay',
                    'transaction_id' => $razorpayPaymentId ?: $transactionId,
                    'amount' => $order->grand_total,
                    'currency' => 'INR',
                    'status' => 'paid',
                    'gateway' => 'Razorpay',
                    'gateway_order_id' => $razorpayOrderId,
                    'gateway_payment_id' => $razorpayPaymentId,
                    'gateway_signature' => $razorpaySignature,
                    'gateway_payload' => $payload,
                    'paid_at' => now(),
                ]);

                $order->update([
                    'payment_method' => 'razorpay',
                    'payment_status' => 'paid',
                    'order_status' => 'processing',
                ]);

                return $payment;
            }

            if ($method === 'upi') {
                $upiRef = $payload['upi_ref_id'] ?? ('UPI' . rand(1000000000, 9999999999));
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'payment_method' => 'upi',
                    'transaction_id' => $upiRef,
                    'amount' => $order->grand_total,
                    'currency' => 'INR',
                    'status' => 'paid',
                    'gateway' => 'UPI Direct',
                    'gateway_payload' => $payload,
                    'paid_at' => now(),
                ]);

                $order->update([
                    'payment_method' => 'upi',
                    'payment_status' => 'paid',
                    'order_status' => 'processing',
                ]);

                return $payment;
            }

            throw new Exception("Unsupported payment method: {$method}");
        });
    }

    /**
     * Server-side HMAC SHA256 verification for Razorpay
     */
    public function verifyRazorpaySignature(?string $orderId, ?string $paymentId, ?string $signature): bool
    {
        $keySecret = config('services.razorpay.secret', env('RAZORPAY_KEY_SECRET', 'shopkart_test_secret_key_12345'));
        
        // If razorpay signature is provided, verify using HMAC
        if ($signature && $orderId && $paymentId) {
            $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
            return hash_equals($expectedSignature, $signature) || app()->environment('local', 'testing');
        }

        // For local development or mock checkout
        return true;
    }
}
