<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'SUSPENDED'])->default('ACTIVE');

            // Subscription & Package
            $table->string('current_package_id')->default('PKG-100');
            $table->string('current_package_name')->default('ميقا 100G');
            $table->decimal('current_package_price', 10, 2)->default(100.00);
            $table->decimal('data_allowance_gb', 10, 2)->default(100.00);
            $table->decimal('used_data_gb', 10, 2)->default(20.00);
            $table->timestamp('expires_at')->nullable();

            // Borrowing / Grace
            $table->enum('borrowing_status', ['NOT_ELIGIBLE', 'AVAILABLE', 'ACTIVE', 'EXPIRED'])->default('NOT_ELIGIBLE');
            $table->decimal('borrowing_used_gb', 10, 2)->default(0.00);
            $table->timestamp('borrowing_expires_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_subscribers');
    }
};
