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
        Schema::table('users', function (Blueprint $table) {
            // Add first_name and last_name, and make name nullable
            $table->string('first_name')->after('name');
            $table->string('last_name')->after('first_name');
            $table->string('name')->nullable()->change();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the added columns and revert name to not nullable
            $table->dropColumn(['first_name', 'last_name']);
            $table->string('name')->nullable(false)->change();
        });
    }
};
