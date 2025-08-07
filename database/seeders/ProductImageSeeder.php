<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = [
            'almond-milk.png',
            'avocado.png',
            'blueberries.png',
            'broccoli.png',
            'brown-eggs.png',
            'butter.png',
            'capers.png',
            'carrots.png',
            'cashew-milk.png',
            'celery.png',
            'chicken-breast.png',
            'coconut.png',
            'coconut-milk.png',
            'coconut-yogurt.png',
            'cow-milk.png',
            'cucumber.png',
            'feta-cheese.png',
            'fresh-dill.png',
            'fresh-parsley.png',
            'green-grapes.png',
            'hazelnut-milk.png',
            'hot-sauce.png',
            'hummus.png',
            'lemon.png',
            'lemon-juice.png',
            'marinated-artichokes.png',
            'mayonnaise-jar-spoon.png',
            'mushrooms.png',
            'mustard-jar.png',
            'oat-milk.png',
            'olives.png',
            'parmesan.png',
            'pesto-jar-spoon.png',
            'pickle-jar.png',
            'raspberries.png',
            'ravioli.png',
            'raw-salmon.png',
            'red-apple.png',
            'red-fruit-jam.png',
            'red-onion.png',
            'red-pepper.png',
            'salsa-sauce.png',
            'sliced-cheddar.png',
            'sliced-mozzarella.png',
            'smoked-salmon.png',
            'spinach.png',
            'strawberries.png',
            'sun-dried-tomatoes.png',
            'tofu.png',
            'tomatoes.png',
            'tortilla-stack.png',
            'tuna-can.png',
            'white-eggs.png',
            'zucchini.png',
        ];

        foreach ($files as $file) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            $name = Str::title(str_replace(['-', '_'], ' ', $basename));
            $imageUrl = "/media/products/$file";

            Product::firstOrCreate(
                ['name' => $name],
                [
                    'barcode' => null,
                    'default_expiry_days' => null,
                    'image_url' => $imageUrl,
                ]
            );
        }
    }
}
