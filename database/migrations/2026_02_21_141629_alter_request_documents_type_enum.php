<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Optional safety: normalize anything weird (you can keep this)
        DB::statement("UPDATE request_documents SET type='business_trip' WHERE type IN ('business trip','BUSINESS_TRIP')");
        DB::statement("UPDATE request_documents SET type='revolving_fund' WHERE type IN ('revolving fund','REVOLVING_FUND','rv_fund')");

        // Now convert to ENUM with ALL valid types
        DB::statement("
            ALTER TABLE request_documents
            MODIFY COLUMN type ENUM(
                'liquidation',
                'payment',
                'business_trip',
                'revolving_fund'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE request_documents MODIFY COLUMN type VARCHAR(50) NOT NULL");
    }
};