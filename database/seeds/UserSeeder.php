<?php

use App\Models\Persona;
use App\Models\UserRol;
use Illuminate\Database\Seeder;
use App\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $persona = Persona::firstOrCreate(
            [
                "tipo_documento_id"=>null,
                "numero_documento"=>"macrotechoslopez",
            ],
            [
                "nombres"=>"Administrador",
                "apellido_paterno"=>"Super",
                "apellido_materno"=>"Admin",
            ]
        );
        $usuario = User::firstOrCreate(
            [
                "persona_id"=>$persona->id,
                "username"=>$persona->numero_documento,

            ],
            [
                //"password"=>$persona->numero_documento,
                "password"=>"macrotechos2024",
            ]
        );

        
    }

    
}
