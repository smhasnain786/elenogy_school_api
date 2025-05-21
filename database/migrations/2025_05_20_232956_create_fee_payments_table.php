<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('assignment_id')->constrained('student_fee_assignments', 'assignment_id')->onDelete('cascade');
            $table->decimal('amount_paid', 10, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50);
            $table->string('transaction_id', 100)->nullable();
            $table->string('payment_proof_url', 255)->nullable();
            $table->string('verification_status', 20)->default('Pending');
            $table->foreignId('verified_by')->constrained('staff', 'staff_id')->onDelete('cascade');
            $table->text('verification_notes')->nullable();
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_payments');
    }
};
