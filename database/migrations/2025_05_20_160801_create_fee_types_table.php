<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id('fee_type_id');
            $table->string('fee_name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->string('frequency', 20);
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_types');
    }
};