<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('failed_jobs')->truncate();
        Schema::table('failed_jobs', static function (Blueprint $table): void {
            $table->dropColumn('id');
        });
        Schema::table('failed_jobs', static function (Blueprint $table): void {
            $table->id();
        });
    }

    public function down(): void
    {
        DB::table('failed_jobs')->truncate();
        Schema::table('failed_jobs', static function (Blueprint $table): void {
            $table->dropColumn('id');
        });
        Schema::table('failed_jobs', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
        });
    }
};
