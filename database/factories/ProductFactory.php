<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(mt_rand(1, 3), true);
        $price = $this->faker->numberBetween(199, 19999);
        $net = (int) round($price * 0.9);
        $tax = $price - $net;

        
return [
            'name' => Str::title($name),
            'slug' => Str::slug($name . '-' . Str::random(6)),
            'description' => $this->faker->boolean(70) ? $this->faker->paragraph() : null,
            'price_cents' => $price,
            'stock' => $this->faker->numberBetween(0, 200),
            'image_path' => $this->faker->boolean(40) ? $this->faker->imageUrl(640, 480, 'product', true) : null,
            'net_price_cents' => round(($price / 100) * 0.9, 2),
            'tax_cents' => round(($price / 100) - round(($price / 100) * 0.9, 2), 2),
            'selling_price_cents' => round($price / 100, 2),        ];
    }
}

