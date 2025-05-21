<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->foreignId('staff_id')->constrained('staff', 'staff_id')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers', 'teacher_id')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes', 'class_id')->onDelete('cascade');
            $table->date('date');
            $table->string('status', 10);
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_attendance');
    }
};