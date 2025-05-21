<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id('holiday_id');
            $table->foreignId('school_id')->constrained('schools', 'school_id')->onDelete('cascade');
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('academic_year', 10);
            $table->string('type', 20);
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_public')->default(true);
            $table->text('description')->nullable();
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('holidays');
    }
};
