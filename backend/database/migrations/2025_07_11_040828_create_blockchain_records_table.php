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
        Schema::create('blockchain_records', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // ex: contract, payment, review
            $table->unsignedBigInteger('reference_id'); // id do registro original
            $table->string('blockchain'); // ex: ethereum, bitcoin
            $table->string('tx_hash')->unique();
            $table->string('status')->default('pending');
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_records');
    }
};
