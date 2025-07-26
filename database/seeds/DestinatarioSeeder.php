<?php

//namespace Database\Seeders;

use App\Models\Destinatario;
use Illuminate\Database\Seeder;

class DestinatarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Destinatario::create([
            "destinatario" => "destinatario 1"
        ]);
        Destinatario::create([
            "destinatario" => "destinatario 2"
        ]);
        Destinatario::create([
            "destinatario" => "destinatario 3"
        ]);
        Destinatario::create([
            "destinatario" => "destinatario 4"
        ]);
    }
}
