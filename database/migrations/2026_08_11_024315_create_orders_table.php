<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'paid', 'processed', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->string('recipient_name');
            $table->string('phone');
            $table->string('country');
            $table->string('city');
            $table->text('address_detail');
            $table->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamp('payment_deadline')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
