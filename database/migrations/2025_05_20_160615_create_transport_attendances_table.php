<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transport_attendance', function (Blueprint $table) {
            $table->id('transport_attendance_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('transport_vehicles', 'vehicle_id')->onDelete('cascade');
            $table->timestamp('boarding_time');
            $table->decimal('boarding_latitude', 9, 6);
            $table->decimal('boarding_longitude', 9, 6);
            $table->timestamp('dropoff_time')->nullable();
            $table->decimal('dropoff_latitude', 9, 6)->nullable();
            $table->decimal('dropoff_longitude', 9, 6)->nullable();
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_attendance');
    }
};
