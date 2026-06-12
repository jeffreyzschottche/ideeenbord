<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('political_preference')->nullable()->after('relationship_status');
            $table->string('household_role')->nullable()->after('political_preference');     // doet de boodschappen?
            $table->string('purchase_decision')->nullable()->after('household_role');         // beslist over aankopen?
            $table->string('order_frequency')->nullable()->after('purchase_decision');         // hoe vaak online bestellen
            $table->string('tech_spend')->nullable()->after('order_frequency');                // maandelijkse uitgaven tech (bucket)
            $table->string('grocery_spend')->nullable()->after('tech_spend');                  // maandelijkse uitgaven boodschappen (bucket)
            $table->string('household_size')->nullable()->after('grocery_spend');              // aantal personen huishouden
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'political_preference',
                'household_role',
                'purchase_decision',
                'order_frequency',
                'tech_spend',
                'grocery_spend',
                'household_size',
            ]);
        });
    }
};
