<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id('structure_id');
            $table->foreignId('fee_type_id')->constrained('fee_types', 'fee_type_id')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools', 'school_id')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('academic_term', 20)->nullable();
            $table->date('due_date');
            $table->decimal('late_fine_per_day', 5, 2)->nullable();
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_structures');
    }
};
