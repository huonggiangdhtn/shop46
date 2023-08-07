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
        Schema::create('warehouseout_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wo_id') ;
            $table->unsignedBigInteger('wto_id')->default(0) ;
            $table->unsignedBigInteger('product_id') ;
            $table->integer('quantity');
            $table->unsignedInteger('price');
            $table->dateTime('expired_at')->nullable();
            $table->string('in_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouseout_details');
    }
};
