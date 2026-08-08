<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('request_documents', function (Blueprint $table) {
    $table->string('approved_signature_path')->nullable()->after('approved_by');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            //
        });
    }
};
