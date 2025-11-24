<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductNutrition extends Model
{
    use HasFactory;

    protected $table = 'product_nutritions';
    protected $fillable = [
        'product_id',
        'short_description',
        'calories',
        'protein',
        'carbs',
        'sugar',
        'fiber',
        'fat',
        'saturated_fat',
        'monounsaturated_fat',
        'polyunsaturated_fat',
        'salt',
        'vitamins_minerals',
        'serving_size',
        'allergens',
        'common_uses',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
