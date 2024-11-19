<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModeradoresTable extends Migration
{

    public function up()
    {
        Schema::create('moderadores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guid')->nullable();
            $table->string('domain')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->softDeletes(); 
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('moderadores');
    }
}