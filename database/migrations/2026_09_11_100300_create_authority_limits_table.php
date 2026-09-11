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
        Schema::create('authority_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->enum('transaction_type', ['withdrawal', 'loan_approval', 'memorial_journal', 'reversal', 'cash_difference', 'penalty_waiver']);
            $table->decimal('max_amount', 18, 2)->default(0);
            $table->boolean('is_unlimited')->default(false);
            $table->timestamps();
            $table->unique(['role_id', 'transaction_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authority_limits');
    }
};
