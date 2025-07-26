<?php

use ColorSeeder as GlobalColorSeeder;
use Database\Seeders\ColorSeeder;
use Database\Seeders\ProductoSeeder;
use Illuminate\Database\Seeder;
use ProductoSeeder as GlobalProductoSeeder;
use TipoRolSeeder as GlobalTipoRolSeeder;

class DatabaseSeeder extends Seeder
{
        /**
         * Seed the application's database.
         *
         * @return void
         */
        public function run()
        {
                $this->call(DepartamentoSeeder::class);
                $this->call(ProvinciaSeeder::class);
                $this->call(DistritoSeeder::class);
                $this->call(UserSeeder::class);
                $this->call(TipoDocumentoSeeder::class);
                $this->call(ProveedorSeeder::class);
                $this->call(DestinatarioSeeder::class);
                $this->call(MarcaSeeder::class);
                $this->call(GlobalColorSeeder::class);
                $this->call(RolSeeder::class);
                $this->call(EmpresaSeeder::class);
                $this->call(AlmacenSeeder::class);
                $this->call(GlobalTipoRolSeeder::class);
        }
}
