<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_buying_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('group_code')->unique()->index();
            $table->unsignedInteger('target_buyers')->default(2);
            $table->unsignedInteger('current_buyers')->default(1);
            $table->decimal('original_price', 10, 2);
            $table->decimal('discounted_price', 10, 2);
            $table->json('discount_tiers')->nullable(); // e.g. [{"buyers":2, "price":949}, {"buyers":5, "price":899}]
            $table->enum('status', ['active', 'completed', 'expired', 'cancelled'])->default('active')->index();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('group_buying_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('group_buying_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('paid_amount', 10, 2);
            $table->enum('status', ['joined', 'confirmed', 'refunded'])->default('joined');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['group_id', 'user_id']); // Prevent duplicate participation
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_buying_participants');
        Schema::dropIfExists('group_buying_groups');
    }
};
