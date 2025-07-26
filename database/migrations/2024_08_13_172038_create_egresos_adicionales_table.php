<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEgresosAdicionalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('egresos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->references('id')->on('empresa');
            $table->string('descripcion')->nullable();
            $table->double('costo')->nullable();
            $table->date('fecha_egreso')->nullable();
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
        Schema::dropIfExists('egresos_adicionales');
    }
}
