<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id('leave_type_id');
            $table->string('name', 50);
            $table->integer('max_days')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_types');
    }
};