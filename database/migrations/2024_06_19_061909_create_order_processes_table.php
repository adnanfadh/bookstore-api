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
        Schema::create('order_processes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->json('item');
            $table->integer('total');
            $table->string('payment_method')->nullable();
            $table->string('recipient')->nullable();
            $table->text('address')->nullable();
            $table->string('delivery_service')->nullable();
            $table->string('order_id')->unique()->nullable();
            $table->boolean('is_paid')->default(false);
            $table->text('payment_proof')->nullable();
            $table->string('status')->default('Pending');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_processes');
    }
};
