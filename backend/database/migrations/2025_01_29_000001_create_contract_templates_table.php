<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('category', ['escrow', 'direct_sale', 'auction']);
            $table->longText('content');
            $table->json('variables'); // Variáveis dinâmicas do template
            $table->string('version', 10)->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
