<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrabajadorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trabajador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->nullable()->references('id')->on('persona');
            //$table->string('direccion_legal')->nullable();
            $table->foreignId('rol_id')->nullable()->references('id')->on('rol');
            $table->foreignId('empresa_id')->nullable()->references('id')->on('empresa');
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
        Schema::dropIfExists('trabajador');
    }
}
