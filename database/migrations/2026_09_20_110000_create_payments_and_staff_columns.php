<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('contract_number')->nullable()->unique()->after('username');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->text('staff_reply')->nullable()->after('internal_notes');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recharge_request_id')->nullable()->constrained('recharge_requests')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('contract_number')->nullable();
            $table->string('subscriber_name')->nullable();
            $table->string('gateway'); // lypay, onepay, bank_transfer, cash
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('gateway');
            $table->index('contract_number');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('staff_reply');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['contract_number']);
            $table->dropColumn('contract_number');
        });
    }
};
