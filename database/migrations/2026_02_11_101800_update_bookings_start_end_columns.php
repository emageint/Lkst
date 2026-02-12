<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'start')) {
                $table->dateTime('start')->nullable()->after('instructor_id');
            }
            if (!Schema::hasColumn('bookings', 'end')) {
                $table->dateTime('end')->nullable()->after('start');
            }
        });

        // Backfill from job_datetime if present
        if (Schema::hasColumn('bookings', 'job_datetime')) {
            DB::statement('UPDATE bookings SET `start` = job_datetime WHERE `start` IS NULL');
            DB::statement('UPDATE bookings SET `end` = job_datetime WHERE `end` IS NULL');
        }

        // Drop old column if exists
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'job_datetime')) {
                $table->dropColumn('job_datetime');
            }
        });
    }


    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'job_datetime')) {
                $table->dateTime('job_datetime')->nullable()->after('instructor_id');
            }
        });

        // Restore job_datetime from start if available
        DB::statement('UPDATE bookings SET job_datetime = `start` WHERE job_datetime IS NULL');

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'start')) {
                $table->dropColumn('start');
            }
            if (Schema::hasColumn('bookings', 'end')) {
                $table->dropColumn('end');
            }
        });
    }
};
