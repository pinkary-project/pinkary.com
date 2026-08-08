<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('pulse_aggregates');
        Schema::dropIfExists('pulse_entries');
        Schema::dropIfExists('pulse_values');
    }
};
