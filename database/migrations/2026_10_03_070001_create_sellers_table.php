<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->string('store_tagline')->nullable();
            $table->string('phone');
            $table->string('business_email')->nullable();
            $table->text('business_address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 10)->index();
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 10, 7)->nullable()->index();
            $table->decimal('delivery_radius_km', 5, 2)->default(15.00);
            $table->unsignedInteger('estimated_delivery_minutes')->default(45);
            $table->boolean('local_delivery_available')->default(true);
            $table->string('gst_number', 30)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc', 20)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('store_logo')->nullable();
            $table->string('store_banner')->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable(); // override platform commission
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->index();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->decimal('trust_score', 4, 1)->default(98.0); // e.g. 98.5%
            $table->unsignedInteger('total_orders')->default(0);
            $table->unsignedInteger('completed_orders')->default(0);
            $table->unsignedInteger('cancelled_orders')->default(0);
            $table->unsignedInteger('on_time_deliveries')->default(0);
            $table->unsignedInteger('return_count')->default(0);
            $table->text('return_policy')->nullable();
            $table->text('about_store')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['pan', 'gst', 'aadhaar', 'cancelled_cheque', 'business_proof']);
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_documents');
        Schema::dropIfExists('sellers');
    }
};
