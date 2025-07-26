<?php

//namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Color::updateOrCreate([
            "nombre" => "RAL3002 - ROJO"
        ]);
        Color::updateOrCreate([
            "nombre" => "RAL5002 - AZUL"
        ]);
        Color::updateOrCreate([
            "nombre" => "RAL5017 - AZUL ELECTRICO"
        ]);
        Color::updateOrCreate([
            "nombre" => "RAL5008 - LADRILLO"
        ]);
        Color::updateOrCreate([
            "nombre" => "RAL3008 - BLANCO"
        ]);
        Color::updateOrCreate([
            "nombre" => "RAL6005 - VERDE"
        ]);
        Color::updateOrCreate([
            "nombre" => "RAL7035 - ALUCIN NATURAL"
        ]);
    }
}
