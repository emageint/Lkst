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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('accrediting_body'); // LKST, CITB, IOSH, UKATA, IPAF, PASMA, NPORS
            $table->text('description')->nullable();
            $table->string('duration'); // half_day, 1_day, multi_day, online
            $table->unsignedInteger('validity_period'); // in months
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
