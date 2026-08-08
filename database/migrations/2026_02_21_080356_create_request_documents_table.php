<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    if (Schema::hasTable('request_documents')) {
        return;
    }

    Schema::create('request_documents', function (Blueprint $table) {
        $table->id();
        $table->string('request_no')->unique();
        $table->enum('type', ['liquidation', 'payment']);
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('department')->nullable();
        $table->string('name')->nullable();
        $table->enum('status', ['draft','submitted','noted','approved','rejected','paid','closed'])->default('draft');
        $table->timestamp('submitted_at')->nullable();
        $table->timestamp('noted_at')->nullable();
        $table->timestamp('approved_at')->nullable();
        $table->timestamp('rejected_at')->nullable();
        $table->unsignedBigInteger('noted_by')->nullable();
        $table->unsignedBigInteger('approved_by')->nullable();
        $table->unsignedBigInteger('rejected_by')->nullable();
        $table->text('rejection_reason')->nullable();
        $table->timestamps();

        $table->index(['type', 'status']);
        $table->index(['user_id', 'status']);
    });
}

    public function down(): void
    {
        Schema::dropIfExists('request_documents');
    }
};