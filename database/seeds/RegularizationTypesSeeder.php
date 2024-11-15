<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegularizationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['name' => 'Missed Punch'],
            ['name' => 'Late Arrival'],
            ['name' => 'Early Departure'],
        ];

        DB::table('type_of_regularizations')->insert($types);
    }
}
