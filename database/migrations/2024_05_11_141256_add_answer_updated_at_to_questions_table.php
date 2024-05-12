<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->timestamp('answer_updated_at')->nullable()->after('answered_at');
        });

        DB::statement('UPDATE questions SET answer_updated_at = updated_at WHERE answered_at < updated_at');
    }
};
