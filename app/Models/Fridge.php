<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fridge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'temperature', 'humidity'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(FridgeItem::class);
    }

    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }
}
