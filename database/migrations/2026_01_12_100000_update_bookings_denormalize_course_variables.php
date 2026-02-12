<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'number_of_learners')) {
                $table->dropColumn('number_of_learners');
            }

            if (! Schema::hasColumn('bookings', 'course_variable_type')) {
                $table->string('course_variable_type')->nullable()->after('training_location_postcode');
            }
            if (! Schema::hasColumn('bookings', 'course_duration')) {
                $table->integer('course_duration')->nullable()->after('course_variable_type');
            }
            if (! Schema::hasColumn('bookings', 'max_delegates')) {
                $table->integer('max_delegates')->nullable()->after('course_duration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'number_of_learners')) {
                $table->integer('number_of_learners')->nullable()->after('training_location_postcode');
            }

            if (Schema::hasColumn('bookings', 'course_variable_type')) {
                $table->dropColumn('course_variable_type');
            }
            if (Schema::hasColumn('bookings', 'course_duration')) {
                $table->dropColumn('course_duration');
            }
            if (Schema::hasColumn('bookings', 'max_delegates')) {
                $table->dropColumn('max_delegates');
            }
        });
    }
};
