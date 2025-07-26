<?php

//namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Rol::updateOrCreate([
            "nombre" => "Administrador Gerente",
            "estado_registro" => "A"
        ]);
        Rol::updateOrCreate([
            "nombre" => "Administrador Trabajador",
            "estado_registro" => "A"
        ]);
        Rol::updateOrCreate([
            "nombre" => "Administrador Cliente",
            "estado_registro" => "A"
        ]);
    }
}
