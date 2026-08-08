<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('liquidation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_document_id')->constrained('request_documents')->cascadeOnDelete();

            $table->string('week_no')->nullable();
            $table->string('form_no')->nullable(); // the "No: 2520" area if you want to store it

            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();

            $table->decimal('cash_advance_amount', 12, 2)->nullable();
            $table->date('cash_advance_date')->nullable();

            $table->decimal('previous_balance', 12, 2)->nullable();
            $table->decimal('starting_balance', 12, 2)->nullable();

            $table->decimal('reimbursement_amount', 12, 2)->nullable();
            $table->decimal('ending_balance', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_details');
    }
};