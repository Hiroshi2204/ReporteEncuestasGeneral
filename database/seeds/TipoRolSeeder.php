<?php

//namespace Database\Seeders;

use App\Models\TipoRol;
use Illuminate\Database\Seeder;

class TipoRolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoRol::updateOrCreate([
            "rol_id" => 1,
            "descripcion" => null,
            "estado_registro" => "A",
        ]);
        TipoRol::updateOrCreate([
            "rol_id" => 2,
            "descripcion" => null,
            "estado_registro" => "A",
        ]);
        TipoRol::updateOrCreate([
            "rol_id" => 3,
            "descripcion" => null,
            "estado_registro" => "A",
        ]);
    }
}
