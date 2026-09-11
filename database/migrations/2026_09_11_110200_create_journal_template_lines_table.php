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
        Schema::create('journal_template_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_template_id')->constrained();
            $table->foreignId('account_id')->constrained();
            $table->enum('side', ['debit', 'credit']);
            $table->string('amount_key', 50);
            $table->enum('office_role', ['origin', 'destination'])->default('origin');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_template_lines');
    }
};
