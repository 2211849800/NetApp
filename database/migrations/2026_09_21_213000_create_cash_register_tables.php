<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_register_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_cash', 10, 2)->default(0);
            $table->decimal('total_card', 10, 2)->default(0);
            $table->decimal('total_transfers', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->timestamp('closed_at');
            $table->timestamps();

            $table->index(['user_id', 'closed_at']);
        });

        Schema::create('cash_register_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->text('note')->nullable();
            $table->foreignId('closed_in_id')->nullable()->constrained('cash_register_closures')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('closed_in_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_register_entries');
        Schema::dropIfExists('cash_register_closures');
    }
};
