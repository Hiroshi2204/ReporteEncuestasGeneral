<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistroSalidaDetalleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registro_salida_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->nullable()->references('id')->on('producto');
            $table->double('precio')->nullable();
            $table->double('largo')->nullable();
            //$table->double('peso_neto')->nullable();
            $table->foreignId('registro_salida_id')->nullable()->references('id')->on('registro_salida');
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
        Schema::dropIfExists('registro_salida_detalle');
    }
}
