<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method')->default('razorpay');
            $table->string('transaction_id')->unique()->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'refunded'])->default('pending')->index();
            $table->string('gateway')->default('Razorpay');
            $table->string('gateway_order_id')->nullable()->index();
            $table->string('gateway_payment_id')->nullable()->index();
            $table->string('gateway_signature')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('refund_number')->unique()->index();
            $table->decimal('amount', 10, 2);
            $table->text('reason');
            $table->json('evidence_images')->nullable();
            $table->enum('status', ['pending', 'seller_approved', 'admin_approved', 'rejected', 'processed'])->default('pending')->index();
            $table->text('seller_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->decimal('order_amount', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('seller_earnings', 10, 2);
            $table->enum('status', ['pending', 'settled', 'refunded'])->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('seller_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->string('payout_number')->unique()->index();
            $table->decimal('gross_sales', 10, 2);
            $table->decimal('commission_deducted', 10, 2);
            $table->decimal('refunds_deducted', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->json('bank_details_snapshot')->nullable();
            $table->enum('status', ['pending', 'processing', 'paid', 'failed'])->default('pending')->index();
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_payouts');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
    }
};
