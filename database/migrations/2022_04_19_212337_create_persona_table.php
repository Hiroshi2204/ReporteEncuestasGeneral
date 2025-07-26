<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->id();
            $table->string('numero_documento')->unique();
            $table->string('nombres')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            //$table->foreignId('rol_id')->nullable()->references('id')->on('rol');
            $table->string('celular')->nullable();
            $table->string('correo')->nullable();
            $table->foreignId('tipo_documento_id')->nullable()->references('id')->on('tipo_documento');
            $table->foreignId('distrito_id')->nullable()->references('id')->on('distritos');
            $table->string('direccion')->nullable();
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
        Schema::dropIfExists('persona');
    }
}
