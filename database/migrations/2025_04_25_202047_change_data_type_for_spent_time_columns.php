<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('projects', static function (Blueprint $table): void {
            $table->bigInteger('spent_time')->unsigned()->default(0)->change();
        });
        Schema::table('tasks', static function (Blueprint $table): void {
            $table->bigInteger('spent_time')->unsigned()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', static function (Blueprint $table): void {
            $table->integer('spent_time')->unsigned()->default(0)->change();
        });
        Schema::table('tasks', static function (Blueprint $table): void {
            $table->integer('spent_time')->unsigned()->default(0)->change();
        });
    }
};
