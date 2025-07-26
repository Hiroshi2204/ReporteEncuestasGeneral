<?php

//namespace Database\Seeders;

use App\Models\Almacen;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Almacen::updateOrCreate([
            "descripcion" => "almacen general",
            "empresa_id" => 1
        ]);
    }
}
