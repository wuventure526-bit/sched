<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_document_id')->constrained('request_documents')->cascadeOnDelete();

            $table->string('payable_to');
            $table->string('address')->nullable();
            $table->date('date')->nullable();

            $table->decimal('total_amount', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_details');
    }
};