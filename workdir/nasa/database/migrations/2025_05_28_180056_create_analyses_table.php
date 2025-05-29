<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedInteger('total_count')->default(0);
            $table->decimal('avg_estimated_diameter_min', 16, 10)->default(0);
            $table->decimal('avg_estimated_diameter_max', 16, 10)->default(0);
            $table->decimal('max_velocity', 24, 12)->default(0);
            $table->decimal('smallest_miss_distance', 24, 12)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
