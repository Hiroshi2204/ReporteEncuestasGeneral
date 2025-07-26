<?php

//namespace Database\Seeders;

use App\Models\Marca;
use Illuminate\Database\Seeder;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Marca::updateOrCreate([
            "nombre" => "SURREY"
        ]);
        Marca::updateOrCreate([
            "nombre" => "CARRIER"
        ]);
        Marca::updateOrCreate([
            "nombre" => "SAMSUNG"
        ]);
        Marca::updateOrCreate([
            "nombre" => "PANASONIC"
        ]);
        Marca::updateOrCreate([
            "nombre" => "TOSHIBA"
        ]);
        Marca::updateOrCreate([
            "nombre" => "LENNOX"
        ]);
        Marca::updateOrCreate([
            "nombre" => "LG"
        ]);
        Marca::updateOrCreate([
            "nombre" => "GOLDSTAR"
        ]);
        Marca::updateOrCreate([
            "nombre" => "HITACHI"
        ]);
    }
}
