<?php

//namespace Database\Seeders;

use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoDocumento::firstOrcreate([
            "nombre"=>"DNI",
            "codigo" => "1",
            "descripcion"=>"Documento Nacional de Identidad",
        ]);
        TipoDocumento::firstOrcreate([
            "nombre"=>"RUC",
            "codigo" => "2",
            "descripcion"=>"Registro Unico Contribuyente",
        ]);
    }
}
