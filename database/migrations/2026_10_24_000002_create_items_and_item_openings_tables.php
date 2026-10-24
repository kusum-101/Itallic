<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Inventory Master: Items (stock items) and their opening stock in each godown.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            // companies.id is `int unsigned` (not the usual bigint), so this must match it
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('name');

            // No onDelete on these three: a unit / stock group / category that an item uses can't be deleted
            $table->foreignId('unit_id')->constrained('units');
            // null = Primary
            $table->foreignId('stock_group_id')->nullable()->constrained('stock_groups');
            // null = no category
            $table->foreignId('stock_category_id')->nullable()->constrained('stock_categories');

            $table->string('hsn_code', 8)->nullable();
            $table->decimal('gst_percent', 5, 2)->nullable();
            $table->timestamps();

            // Same item name allowed in different companies, but not twice in the same company
            $table->unique(['company_id', 'name']);
        });

        // Opening stock: how much of an item each godown had when the company started using the app
        Schema::create('item_openings', function (Blueprint $table) {
            $table->id();
            // Opening rows go with their item
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            // No onDelete: a godown that has stock in it can't be deleted
            $table->foreignId('godown_id')->constrained('godowns');
            // 4 digits after the point: a unit can allow up to 4 (units.decimals)
            $table->decimal('quantity', 15, 4);
            $table->decimal('rate', 15, 2)->default(0);
            // Quantity x Rate, worked out when saving
            $table->decimal('value', 15, 2)->default(0);
            $table->timestamps();

            // One row per godown for an item
            $table->unique(['item_id', 'godown_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_openings');
        Schema::dropIfExists('items');
    }
};
