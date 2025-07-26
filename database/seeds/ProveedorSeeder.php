<?php

//namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Proveedor::create([
            "proveedor" => "proveedor 1"
        ]);
        Proveedor::create([
            "proveedor" => "proveedor 2"
        ]);
        Proveedor::create([
            "proveedor" => "proveedor 3"
        ]);
        Proveedor::create([
            "proveedor" => "proveedor 4"
        ]);
    }
}
