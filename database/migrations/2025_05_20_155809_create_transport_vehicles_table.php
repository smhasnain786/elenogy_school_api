<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id('vehicle_id');
            $table->string('vehicle_number', 20)->unique();
            $table->foreignId('driver_id')->constrained('staff', 'staff_id')->onDelete('cascade');
            $table->integer('capacity');
            $table->jsonb('route_details');
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_vehicles');
    }
};
