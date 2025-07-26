<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistroSalidaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registro_salida', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_salida')->nullable();
            $table->foreignId('destinatario_id')->nullable()->references('id')->on('destinatario');
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
        Schema::dropIfExists('registro_salida');
    }
}
