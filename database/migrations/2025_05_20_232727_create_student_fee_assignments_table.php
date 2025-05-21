<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_fee_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->foreignId('structure_id')->constrained('fee_structures', 'structure_id')->onDelete('cascade');
            $table->decimal('waived_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_fee_assignments');
    }
};
