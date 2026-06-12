<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('site_contact_name')->nullable()->after('location_lkst_yard');
            $table->string('site_contact_number')->nullable()->after('site_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['site_contact_name', 'site_contact_number']);
        });
    }
};
