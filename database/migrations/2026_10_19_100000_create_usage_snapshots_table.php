<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->index();
            $table->date('usage_date');
            $table->decimal('used_gb', 10, 2);
            $table->timestamps();

            $table->unique(['contract_number', 'usage_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_snapshots');
    }
};
