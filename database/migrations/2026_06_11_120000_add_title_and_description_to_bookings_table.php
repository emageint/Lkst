<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'title')) {
                $table->string('title')->nullable()->after('course_id');
            }
            if (! Schema::hasColumn('bookings', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });

        // Miscellaneous bookings have no course/customer, so allow these to be null.
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->change();
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });
    }
};
