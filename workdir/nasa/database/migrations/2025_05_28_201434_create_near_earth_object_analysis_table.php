<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('near_earth_object_analysis', function (Blueprint $table) {

            $table->unsignedBigInteger('neo_id');
            $table->unsignedBigInteger('analysis_id');

            $table->foreign('neo_id')
                ->references('id')
                ->on('near_earth_objects')
                ->onDelete('cascade');

            $table->foreign('analysis_id')
                ->references('id')
                ->on('analyses')
                ->onDelete('cascade');

            $table->primary(['neo_id', 'analysis_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('near_earth_object_analysis');
    }
};
