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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices');
            $table->foreignId('reversal_of_id')->nullable()->unique()->constrained('journals');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->string('number', 50)->unique();
            $table->date('book_date');
            $table->string('transaction_type', 50)->nullable();
            $table->string('description');
            $table->nullableMorphs('source');
            $table->uuid('inter_office_group')->nullable()->index();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->enum('status', ['draft', 'pending_approval', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->index(['office_id', 'book_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
