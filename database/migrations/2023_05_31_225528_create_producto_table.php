<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->id();
            $table->string('nom_producto')->nullable();
            $table->string('cod_producto')->nullable();
            $table->foreignId('color_id')->nullable()->references('id')->on('color');
            $table->string('lote')->nullable();
            $table->double('largo')->nullable();
            $table->string('espesor')->nullable();
            //$table->string('peso_neto')->nullable();
            //$table->foreignId('marca_id')->nullable()->references('id')->on('marca');
            $table->char('estado_registro')->default('A');
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
        Schema::dropIfExists('producto');
    }
}
