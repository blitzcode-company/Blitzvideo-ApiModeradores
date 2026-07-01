<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActividadModeracionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actividad_moderacions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('moderador_id');
            $table->string('moderador_nombre'); 
            $table->string('accion');
            $table->unsignedBigInteger('reporte_id')->nullable(); 
            $table->unsignedBigInteger('comentario_id')->nullable(); 
            $table->unsignedBigInteger('video_id')->nullable(); 
            $table->unsignedBigInteger('user_id')->nullable(); 

            $table->text('detalles')->nullable(); 
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
        Schema::dropIfExists('actividad_moderacions');
    }
}
