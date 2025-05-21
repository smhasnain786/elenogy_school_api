<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id('payroll_id');
            $table->foreignId('salary_id')->constrained('salary_structures', 'salary_id')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('net_amount', 12, 2);
            $table->string('payment_method', 20);
            $table->string('transaction_id', 100)->nullable();
            $table->jsonb('bank_account_details')->nullable();
            $table->jsonb('tax_details')->nullable();
            $table->string('payment_status', 20)->default('Pending');
            $table->text('remarks')->nullable();
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_records');
    }
};
