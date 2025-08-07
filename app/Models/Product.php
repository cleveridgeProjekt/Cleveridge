<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'barcode', 'default_expiry_days', 'image_url'
    ];

    public function fridgeItems()
    {
        return $this->hasMany(FridgeItem::class);
    }

    public function shoppingListItems()
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    public function usersMustHave()
    {
        return $this->belongsToMany(User::class, 'user_must_have_products');
    }

    public function usersWithAllergy()
    {
        return $this->belongsToMany(User::class, 'user_allergies');
    }

}
