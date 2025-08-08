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
        Schema::create('product_nutritions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->text('short_description')->nullable();

            $table->float('calories')->nullable();
            $table->float('protein')->nullable();
            $table->float('carbs')->nullable();
            $table->float('sugar')->nullable();
            $table->float('fiber')->nullable();
            $table->float('fat')->nullable();
            $table->float('saturated_fat')->nullable();
            $table->float('monounsaturated_fat')->nullable();
            $table->float('polyunsaturated_fat')->nullable();
            $table->float('salt')->nullable();

            $table->text('vitamins_minerals')->nullable();
            $table->string('serving_size', 255)->nullable();
            $table->string('allergens', 255)->nullable();
            $table->text('common_uses')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_nutritions');
    }
};
