<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('liquidation_purposes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_document_id')->constrained('request_documents')->cascadeOnDelete();

            $table->string('purpose'); // e.g. business_travel_allowance
            $table->string('other_text')->nullable();

            $table->timestamps();

            $table->index('request_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_purposes');
    }
};