<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id('application_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types', 'leave_type_id')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('status', 20)->default('Pending');
            $table->foreignId('approver_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->char('CDC_FLAG', 1)->default('A');
            $table->date('valid_from')->default(DB::raw('CURRENT_DATE'));
            $table->date('valid_to')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_applications');
    }
};
