<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('fridge_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fridge_id');
            $table->unsignedBigInteger('product_id');
            $table->date('expiry_date')->nullable();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->foreign('fridge_id')->references('id')->on('fridges')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fridge_items');
    }
};
