<?php

declare(strict_types=1);

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
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Run the migrations.
     *
     * Without this, `migrate:rollback` deletes the ledger row but leaves the
     * table behind, and the next `migrate` aborts with "table already exists"
     * -- so one rollback blocks every subsequent deploy until someone drops
     * it by hand. This is the table the entire API auth surface depends on,
     * and the one most likely to need a fast rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
