<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run()
    {
        $json = file_get_contents(database_path('data/cities.json'));
        $cities = json_decode($json, true);

        foreach ($cities as $city) {
            DB::table('areas')->insert([
                'id' => $city['id'], // لو auto increment احذف السطر ده
                'name_ar' => $city['city_name_ar'],
                'name_en' => $city['city_name_en'],
                'governorate_id' => $city['governorate_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}