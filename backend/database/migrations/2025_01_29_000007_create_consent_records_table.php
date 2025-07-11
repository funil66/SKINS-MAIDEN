<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('consent_type', [
                'data_processing',
                'marketing_communications',
                'analytics_tracking',
                'third_party_sharing',
                'cookies_functional',
                'cookies_analytics',
                'cookies_marketing',
                'geolocation_tracking',
                'push_notifications'
            ]);
            $table->boolean('granted');
            $table->text('purpose_description'); // Descrição específica do uso
            $table->enum('legal_basis', [
                'consent',
                'contract',
                'legal_obligation',
                'vital_interests',
                'public_task',
                'legitimate_interests'
            ])->default('consent');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->string('consent_version', 10)->default('1.0'); // Versão dos termos
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['consent_type']);
            $table->index(['granted']);
            $table->index(['granted_at']);
            $table->index(['expires_at']);
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
