<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_reports', function (Blueprint $table) {
            // 'all' = volledige historie, 'monthly' = maandbereik, 'custom' = vrij datumbereik
            $table->string('period_type')->default('all')->after('provider');
            $table->date('period_start')->nullable()->after('period_type');
            $table->date('period_end')->nullable()->after('period_start');
        });
    }

    public function down(): void
    {
        Schema::table('brand_reports', function (Blueprint $table) {
            $table->dropColumn(['period_type', 'period_start', 'period_end']);
        });
    }
};
