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
        Schema::create('maintenance_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id') ;
            $table->unsignedBigInteger('customer_id') ;
            $table->integer('quantity') ;
            $table->integer('sent')->default(0) ;
            $table->string('description')->nullable() ;
            $table->integer('shipcost')->nullable() ;
            $table->bigInteger('final_amount') ;
            $table->bigInteger('paid_amount') ;
            $table->unsignedBigInteger('vendor_id') ;
            $table->enum('result',['pending','damaged','ok'])->default('pending');
            $table->enum('status',['received','sent','back','returned'])->default('received');
            $table->unsignedBigInteger('shiptrans_id')->nullable() ;
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_ins');
    }
};
