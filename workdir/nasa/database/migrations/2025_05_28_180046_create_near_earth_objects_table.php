<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('near_earth_objects', function (Blueprint $table) {
            $table->id();
            $table->string('ref_id')->unique();
            $table->string('name');
            $table->date('date')->nullable()->default(null);
            $table->decimal('estimated_diameter_min', 16, 10);
            $table->decimal('estimated_diameter_max', 16, 10);
            $table->boolean('is_hazardous')->default(false);
            $table->decimal('abs_magnitude', 6, 2)->default(0);
            $table->decimal('velocity', 24, 10)->default(0);
            $table->decimal('miss_distance', 24, 10)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('near_earth_objects');
    }
};
