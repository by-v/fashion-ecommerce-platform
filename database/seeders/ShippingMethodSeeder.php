<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'JNE Reguler', 'cost' => 15000, 'estimated_days' => 3],
            ['name' => 'JNE YES (Express)', 'cost' => 30000, 'estimated_days' => 1],
            ['name' => 'J&T Express', 'cost' => 17000, 'estimated_days' => 2],
        ];

        foreach ($methods as $method) {
            ShippingMethod::create($method);
        }
    }
}
