<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistroEntradaDetalleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registro_entrada_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->nullable()->references('id')->on('producto');
            //$table->string('origen')->nullable();
            //$table->double('peso_neto')->nullable();
            $table->double('largo')->nullable();
            $table->double('precio')->nullable();
            //$table->string('lote')->nullable();
            //$table->integer('cantidad')->nullable();
            $table->foreignId('registro_entrada_id')->nullable()->references('id')->on('registro_entrada');
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
        Schema::dropIfExists('registro_entrada_detalle');
    }
}
