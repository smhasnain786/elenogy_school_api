<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id('payslip_id');
            $table->foreignId('payroll_id')->constrained('payroll_records', 'payroll_id')->onDelete('cascade');
            $table->foreignId('document_id')->constrained('documents', 'doc_id')->onDelete('cascade');
            $table->string('payslip_period', 20);
            $table->date('generation_date')->default(DB::raw('CURRENT_DATE'));
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslips');
    }
};
