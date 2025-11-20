<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'course_id')) {
            // Move existing user->course to pivot
            $pairs = DB::table('users')
                ->whereNotNull('course_id')
                ->select('id as user_id', 'course_id')
                ->get();

            foreach ($pairs as $row) {
                DB::table('course_user')->updateOrInsert(
                    [
                        'user_id' => $row->user_id,
                        'course_id' => $row->course_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Drop the foreign key and column
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('course_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'course_id')) {
            // Recreate the single course_id column
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('course_id')
                    ->nullable()
                    ->constrained('courses')
                    ->nullOnDelete();
            });

            // Move one course back to users (choose the most recently attached)
            $latestPerUser = DB::table('course_user')
                ->select('user_id', DB::raw('MAX(created_at) as latest'))
                ->groupBy('user_id');

            $rows = DB::table('course_user as cu')
                ->joinSub($latestPerUser, 'l', function ($join) {
                    $join->on('cu.user_id', '=', 'l.user_id')
                         ->on('cu.created_at', '=', 'l.latest');
                })
                ->select('cu.user_id', 'cu.course_id')
                ->get();

            foreach ($rows as $row) {
                DB::table('users')->where('id', $row->user_id)->update(['course_id' => $row->course_id]);
            }
        }
    }
};
