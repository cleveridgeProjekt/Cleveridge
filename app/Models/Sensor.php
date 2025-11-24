<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = ['fridge_id', 'temperature', 'humidity'];

    public function fridge()
    {
        return $this->belongsTo(Fridge::class);
    }
}
