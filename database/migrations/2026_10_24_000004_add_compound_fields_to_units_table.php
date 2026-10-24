<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Compound units, like Tally: two simple units joined by a conversion,
     * e.g. "Box of 12 Pcs" = First Unit Box, Conversion 12, Second Unit Pcs (1 Box = 12 Pcs).
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Simple (Nos, Kg, ...) or Compound (Box of 12 Pcs)
            $table->string('type', 10)->default('Simple')->after('company_id');

            // A compound unit's symbol holds both symbols and the conversion
            // (20 + " of " + 16 + " " + 20), so 20 characters is not enough any more
            $table->string('symbol', 70)->change();

            // Only for a Compound unit. No onDelete: a simple unit that a compound unit uses can't be deleted
            $table->foreignId('first_unit_id')->nullable()->after('decimals')->constrained('units');
            // How many of the second unit make one of the first unit, e.g. 12
            $table->decimal('conversion', 15, 4)->nullable()->after('first_unit_id');
            $table->foreignId('second_unit_id')->nullable()->after('conversion')->constrained('units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Without these columns a compound unit means nothing (and its long symbol wouldn't fit)
        DB::table('units')->where('type', 'Compound')->delete();

        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['first_unit_id']);
            $table->dropForeign(['second_unit_id']);
            $table->dropColumn(['type', 'first_unit_id', 'conversion', 'second_unit_id']);
            $table->string('symbol', 20)->change();
        });
    }
};
