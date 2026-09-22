<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recharge_requests', function (Blueprint $table) {
            $table->string('contract_number')->nullable()->after('user_id');
            $table->string('subscriber_name')->nullable()->after('contract_number');
            $table->string('package_id')->nullable()->after('subscriber_name');
            $table->string('package_name')->nullable()->after('package_id');
            $table->decimal('amount', 10, 2)->nullable()->after('package_name');
            $table->string('payment_method')->nullable()->after('amount');
            $table->string('receipt_path')->nullable()->after('payment_method');
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('processed_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable()->after('processed_by');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->string('contract_number')->nullable()->after('user_id');
            $table->string('subscriber_name')->nullable()->after('contract_number');
            $table->string('type')->nullable()->after('subject');
            $table->string('priority')->default('medium')->after('type');
            $table->text('description')->nullable()->after('priority');
            $table->text('internal_notes')->nullable()->after('status');
            $table->foreignId('assigned_to')->nullable()->after('internal_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('assigned_to');
        });

        Schema::create('subscription_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('applicant_name');
            $table->string('national_id')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('package_id')->nullable();
            $table->string('package_name')->nullable();
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->text('internal_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('phone');
        });

        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contract_number');
            $table->string('current_owner_name');
            $table->string('new_owner_name');
            $table->string('new_national_id')->nullable();
            $table->string('new_phone');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->text('internal_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('contract_number');
        });

        Schema::create('operational_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, warning, success, alert
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_notifications');
        Schema::dropIfExists('transfer_requests');
        Schema::dropIfExists('subscription_requests');

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'contract_number',
                'subscriber_name',
                'type',
                'priority',
                'description',
                'internal_notes',
                'assigned_to',
                'resolved_at',
            ]);
        });

        Schema::table('recharge_requests', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn([
                'contract_number',
                'subscriber_name',
                'package_id',
                'package_name',
                'amount',
                'payment_method',
                'receipt_path',
                'notes',
                'processed_by',
                'processed_at',
            ]);
        });
    }
};
