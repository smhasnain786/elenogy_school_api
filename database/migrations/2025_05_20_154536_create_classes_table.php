<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id('class_id');
            $table->foreignId('subject_id')->constrained('subjects', 'subject_id')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers', 'teacher_id')->onDelete('cascade');
            $table->string('grade_level', 10);
            $table->string('section', 10);
            $table->string('academic_year', 10);
            $table->jsonb('schedule');
            $table->string('room_number', 20)->nullable();
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('classes');
    }
};