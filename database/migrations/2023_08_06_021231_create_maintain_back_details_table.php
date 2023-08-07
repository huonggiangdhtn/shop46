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
        Schema::create('maintain_back_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mb_id') ;
            $table->unsignedBigInteger('product_id') ;
            $table->integer('quantity');
            $table->string('in_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintain_back_details');
    }
};
