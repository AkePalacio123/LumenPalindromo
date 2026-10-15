<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('trip_number', 40)->unique();
            $table->string('origin');
            $table->string('destination');
            $table->string('cargo')->nullable();
            $table->dateTime('departure_at')->nullable();
            $table->dateTime('eta')->nullable();
            $table->string('status', 24)->default('Programado');
            $table->unsignedBigInteger('vehicle_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->decimal('distance_km', 9, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trips');
    }
};
