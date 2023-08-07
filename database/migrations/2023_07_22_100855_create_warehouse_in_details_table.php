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
        Schema::create('warehouse_in_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wi_id') ;
            $table->unsignedBigInteger('wti_id')->default(0) ;
            $table->unsignedBigInteger('wh_id')->default(0) ;
            $table->unsignedBigInteger('product_id') ;
            $table->integer('quantity');
            $table->unsignedInteger('price');
            $table->integer('qty_sold')->default(0);
            $table->dateTime('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_in_details');
    }
};
