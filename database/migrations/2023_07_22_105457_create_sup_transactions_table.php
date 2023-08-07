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
        Schema::create('sup_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id') ;
            $table->unsignedBigInteger('doc_id')->default(0) ;
            $table->enum('doc_type',['wi','wo','fi','si','so','mi','mo']);
            $table->integer('operation') ;
            $table->BigInteger('amount') ;
            $table->BigInteger('total') ;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sup_transactions');
    }
};
