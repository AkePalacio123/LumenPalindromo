<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fuel_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->decimal('liters', 10, 2);
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('odometer_km')->nullable();
            $table->string('station')->nullable();
            $table->dateTime('refueled_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fuel_records');
    }
};
