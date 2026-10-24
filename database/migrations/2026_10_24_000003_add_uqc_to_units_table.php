<?php

use App\Models\Unit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * UQC (Unit Quantity Code): the GST code of a unit, e.g. KGS for Kg.
     * GST returns and e-way bills show the quantity with this code.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // One of Unit::UQCS, e.g. KGS. null = Not Applicable
            $table->string('uqc', 3)->nullable()->after('name');
        });

        // The default units already made (Nos, Kg, ...) get their UQC too
        foreach (Unit::DEFAULTS as $symbol => [$name, $decimals, $uqc]) {
            DB::table('units')->where('symbol', $symbol)->whereNull('uqc')->update(['uqc' => $uqc]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('uqc');
        });
    }
};
