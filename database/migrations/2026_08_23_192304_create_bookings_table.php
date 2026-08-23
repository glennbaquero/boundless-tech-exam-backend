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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // "One-way" / "Hourly"
            $table->string('service_type')->default('one_way');

            // Pickup panel
            $table->date('pickup_date');
            $table->time('pickup_time');
            $table->string('pickup_type')->default('location'); // location | airport
            $table->string('pickup_location');
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();

            // Drop off panel
            $table->string('dropoff_type')->default('location'); // location | airport
            $table->string('dropoff_location');
            $table->decimal('dropoff_lat', 10, 7)->nullable();
            $table->decimal('dropoff_lng', 10, 7)->nullable();

            $table->unsignedInteger('passengers')->default(1);

            // Google Maps Distance Matrix result, cached at submission time.
            $table->unsignedInteger('distance_meters')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('distance_text')->nullable();
            $table->string('duration_text')->nullable();

            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
