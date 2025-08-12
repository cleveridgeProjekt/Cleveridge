<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FridgeItem extends Model
{
    use HasFactory;

    protected $fillable = ['fridge_id','product_id','expiry_date','quantity'];

    protected $casts = [
        'expiry_date' => 'date:Y-m-d',
    ];

    public function fridge()
    {
        return $this->belongsTo(Fridge::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function scopeExpired($q)
    {
        $from = today()->toDateString();
        return $q->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $from);
    }

    public function scopeExpiringSoon($q, $days = 3)
    {
        $from = today()->toDateString();
        $to   = today()->addDays((int) max($days, 1))->toDateString();
        return $q->whereNotNull('expiry_date')->whereBetween('expiry_date', [$from, $to]);
    }
}
