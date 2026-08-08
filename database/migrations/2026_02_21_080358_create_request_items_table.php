<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_document_id')->constrained('request_documents')->cascadeOnDelete();

            $table->date('item_date')->nullable(); // liquidation uses date column
            $table->string('particulars');
            $table->decimal('amount', 12, 2);

            $table->timestamps();

            $table->index('request_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_items');
    }
};