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
        Schema::create('pan_analytics', function (Blueprint $table): void {
            $table->id();
            $table->string('name');

            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('hovers')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pan_analytics');
    }
};
