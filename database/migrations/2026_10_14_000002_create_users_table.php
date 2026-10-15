<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 40)->nullable();
            $table->string('password');
            $table->string('role', 40)->default('Operador');
            $table->string('status', 24)->default('Activo');
            $table->string('base')->nullable();
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
