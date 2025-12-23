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
        Schema::table('training_jobs', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['tutor_id']);
            
            // Rename the column
            $table->renameColumn('tutor_id', 'instructor_id');
            
            // Recreate the foreign key with the new column name
            $table->foreign('instructor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            // Drop the old index and create a new one
            $table->dropIndex(['status', 'tutor_id']);
            $table->index(['status', 'instructor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_jobs', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['instructor_id']);
            
            // Drop the new index
            $table->dropIndex(['status', 'instructor_id']);
            
            // Rename the column back
            $table->renameColumn('instructor_id', 'tutor_id');
            
            // Recreate the original foreign key
            $table->foreign('tutor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            // Recreate the original index
            $table->index(['status', 'tutor_id']);
        });
    }
};
