<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Every stock category now belongs to a stock group (picked on the form, required there).
     * Nullable only for categories made before this: they get their group when next edited.
     */
    public function up(): void
    {
        Schema::table('stock_categories', function (Blueprint $table) {
            // A stock group with categories in it can't be deleted, so no cascade here
            $table->foreignId('stock_group_id')->nullable()->after('company_id')->constrained('stock_groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_group_id');
        });
    }
};
