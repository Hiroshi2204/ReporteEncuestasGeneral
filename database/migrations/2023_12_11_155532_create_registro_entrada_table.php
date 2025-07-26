<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistroEntradaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registro_entrada', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_entrada')->nullable();
            $table->foreignId('proveedor_id')->nullable()->references('id')->on('proveedor');
            $table->foreignId('almacen_id')->nullable()->references('id')->on('almacen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registro_entrada');
    }
}
