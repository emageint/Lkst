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
        Schema::create('training_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('customer_id')->constrained('users')->comment('The customer who requested the training');
            $table->foreignId('tutor_id')->constrained('users')->comment('The tutor assigned to this job');
            
            // Training location address
            $table->string('training_location_line1');
            $table->string('training_location_line2')->nullable();
            $table->string('training_location_line3')->nullable();
            $table->string('training_location_city');
            $table->string('training_location_postcode');
            
            // Other fields
            $table->unsignedInteger('number_of_learners');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'tutor_id']);
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_jobs');
    }
};
