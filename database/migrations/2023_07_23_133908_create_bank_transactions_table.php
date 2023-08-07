<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('total') ;
            $table->unsignedBigInteger('bank_id') ;
            $table->integer('operation') ;
            $table->unsignedBigInteger('doc_id') ;
            $table->enum('doc_type',['wi','wo','fi','si','so','mi','mo']);
            $table->unsignedBigInteger('user_id') ;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
