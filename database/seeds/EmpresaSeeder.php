<?php

//namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Empresa::updateOrCreate([
            "numero_documento" => "20536294237",
            "razon_social" => "MACROTECHOS LOPEZ S.A.C",
            //"logo" => "",
            "distrito_id" => 1282,
            "direccion_legal" => "HUANCAYO"
        ]);
    }
}
