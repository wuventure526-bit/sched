<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_trip_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_document_id')->unique();

            $table->string('driver_name')->nullable();
            $table->string('vehicle_plate_no')->nullable();

            $table->string('speedometer_begin')->nullable();
            $table->string('speedometer_end')->nullable();

            $table->decimal('total_mileage_km', 10, 2)->nullable();

            $table->date('trip_date')->nullable();
            $table->time('time_out')->nullable();
            $table->time('time_in')->nullable();

            $table->text('purpose')->nullable();

            $table->string('checked_by')->nullable();
            $table->string('noted_by')->nullable();

            $table->timestamps();

            $table->foreign('request_document_id')
                ->references('id')->on('request_documents')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_trip_details');
    }
};