<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            ['name' => 'برج القراءة'],
            ['name' => 'برج الخبرة'],
            ['name' => 'برج الفلسفة'],
            ['name' => 'برج السينما'],
        ];

        DB::table('divisions')->insert($divisions);
    }
}
