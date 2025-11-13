<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::firstOrCreate(
            ['email' => 'customer@customer.com'],
            [
                'name' => 'Customer',
                'phone' => '+84123456789',
                'address' => 'Địa chỉ',
                'is_active' => true,
            ]
        );

        Customer::factory()->count(10)->create();
    }
}
