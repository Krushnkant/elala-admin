<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use Illuminate\Database\Seeder;

class AgeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AgeGroup::create([
            'from_age' => 0,
            'to_age' => 10,
            'is_delete' => 1
        ]);
    }
}
