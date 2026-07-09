<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan',     'emoji' => '🍔', 'color' => '#ef4444'],
            ['name' => 'Transport',   'emoji' => '🛵', 'color' => '#f97316'],
            ['name' => 'Belanja',     'emoji' => '🛒', 'color' => '#eab308'],
            ['name' => 'Tagihan',     'emoji' => '📄', 'color' => '#22c55e'],
            ['name' => 'Hiburan',     'emoji' => '🎬', 'color' => '#3b82f6'],
            ['name' => 'Kesehatan',   'emoji' => '💊', 'color' => '#a855f7'],
            ['name' => 'Lainnya',     'emoji' => '📌', 'color' => '#6b7280'],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['is_default' => true]));
        }
    }
}
