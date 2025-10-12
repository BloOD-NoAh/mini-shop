<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['Vintage Camera', 'Photography', 12999, 'A classic film camera with retro charm.', 'images/ph1.svg'],
            ['Wireless Headphones', 'Audio', 8999, 'Comfortable over-ear headphones with rich sound.', 'images/ph2.svg'],
            ['Smartwatch', 'Wearables', 15999, 'Track fitness, notifications, and more on your wrist.', 'images/ph3.svg'],
            ['Portable Speaker', 'Audio', 5999, 'Compact speaker with surprisingly big sound.', 'images/ph4.svg'],
            ['Gaming Mouse', 'Peripherals', 4999, 'Ergonomic design with customizable buttons.', 'images/ph1.svg'],
            ['Mechanical Keyboard', 'Peripherals', 11999, 'Tactile switches for a satisfying typing experience.', 'images/ph2.svg'],
            ['4K Monitor', 'Displays', 32999, 'Crisp, color-accurate 27-inch 4K display.', 'images/ph3.svg'],
            ['External SSD 1TB', 'Storage', 13999, 'Fast portable storage for your files.', 'images/ph4.svg'],
            ['USB-C Hub', 'Accessories', 3999, 'Expand your laptop connectivity in one device.', 'images/ph1.svg'],
            ['Webcam HD', 'Accessories', 6999, 'Sharp video calls with automatic light correction.', 'images/ph2.svg'],
            ['Desk Lamp', 'Accessories', 2999, 'Adjustable LED lamp with warm and cool modes.', 'images/ph3.svg'],
            ['Laptop Stand', 'Accessories', 3499, 'Raise your laptop for better ergonomics.', 'images/ph4.svg'],
        ];

        foreach ($samples as [$name, $category, $price, $desc, $image]) {
            $net = (int) round($price * 0.9);
            $tax = $price - $net;

            Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'category' => $category,
                    'description' => $desc,
                    'price_cents' => $price,
                    'stock' => 25,
                    'image_path' => $image,
                    'net_price_cents' => round(($price / 100) * 0.9, 2),
                    'tax_cents' => round(($price / 100) - round(($price / 100) * 0.9, 2), 2),
                    'selling_price_cents' => round($price / 100, 2),
                ]
            );
        }
    }
}


