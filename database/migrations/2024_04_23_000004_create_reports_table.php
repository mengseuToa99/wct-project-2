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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_id');
            $table->string('status');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('report_detail_id');
            $table->unsignedBigInteger('category_id');

            // relation to other table
            $table->foreign('reporter_id')->references('id')->on('reporters');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('report_detail_id')->references('id')->on('report_details');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
