<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the product seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Sản phẩm 1',
                'description' => 'Mô tả sản phẩm 1',
                'price' => 1299.99,
                'stock' => 50,
                'sku' => 'SKU-001',
                'image' => 'image1.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Sản phẩm 2',
                'description' => 'Mô tả sản phẩm 2',
                'price' => 999.99,
                'stock' => 100,
                'sku' => 'SKU-002',
                'image' => 'image2.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Sản phẩm 3',
                'description' => 'Mô tả sản phẩm 3',
                'price' => 899.99,
                'stock' => 75,
                'sku' => 'SKU-003',
                'image' => 'image3.jpg',
                'is_active' => true,
            ],
            
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
