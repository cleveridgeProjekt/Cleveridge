<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FridgeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fridge_id', 'product_id', 'expiry_date', 'quantity'
    ];

    public function fridge()
    {
        return $this->belongsTo(Fridge::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
