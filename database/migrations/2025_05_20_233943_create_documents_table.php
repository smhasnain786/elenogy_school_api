<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id('doc_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->string('doc_type', 50);
            $table->string('category', 20);
            $table->string('file_path', 255);
            $table->integer('version')->default(1);
            $table->foreignId('previous_version')->constrained('documents', 'doc_id')->onDelete('cascade');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->char('CDC_FLAG', 1)->default('A');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
