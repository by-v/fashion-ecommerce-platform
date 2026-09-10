<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        $products = [
            ['name' => 'Gamis Syari Rayon Premium', 'price' => 249000, 'featured' => true],
            ['name' => 'Hijab Segiempat Voal', 'price' => 65000, 'featured' => true],
            ['name' => 'Tunik Katun Linen', 'price' => 189000, 'featured' => false],
            ['name' => 'Setelan Batik Modern', 'price' => 275000, 'featured' => false],
            ['name' => 'Gamis Bordir Mewah', 'price' => 349000, 'featured' => true],
            ['name' => 'Hijab Pashmina Ceruty', 'price' => 55000, 'featured' => false],
        ];

        foreach ($products as $item) {
            $product = Product::create([
                'category_id' => $categories->random()->id,
                'name' => $item['name'],
                'slug' => Str::slug($item['name']) . '-' . uniqid(),
                'description' => 'Deskripsi produk ' . $item['name'] . '. Bahan berkualitas, nyaman dipakai sehari-hari.',
                'price' => $item['price'],
                'stock' => rand(5, 50),
                'image' => null,
                'is_featured' => $item['featured'],
            ]);

            // Tambah variant size untuk tiap produk (khusus fashion)
            foreach (['S', 'M', 'L', 'XL'] as $size) {
                $product->variants()->create([
                    'size' => $size,
                    'stock' => rand(2, 15),
                ]);
            }
        }
    }
}
