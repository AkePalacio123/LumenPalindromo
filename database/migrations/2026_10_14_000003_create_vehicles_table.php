<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('unit_number', 32)->unique();
            $table->string('plate', 32)->unique();
            $table->string('make', 80)->nullable();
            $table->string('model', 80)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->decimal('capacity_tons', 8, 2)->nullable();
            $table->string('status', 24)->default('Disponible');
            $table->string('last_location')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
