<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = database_path('departamentos.sql');
        DB::unprepared(file_get_contents($sql));
    }
}
