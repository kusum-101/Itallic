<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Inventory Master: Stock Groups and Stock Categories, two ways of sorting items.
     * Both work like account groups: a row sits under another one, or at the top (Primary).
     */
    public function up(): void
    {
        Schema::create('stock_groups', function (Blueprint $table) {
            $table->id();
            // companies.id is `int unsigned` (not the usual bigint), so this must match it
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // null = Primary (top level). A group with sub-groups can't be deleted, so no cascade here
            $table->foreignId('parent_id')->nullable()->constrained('stock_groups');
            $table->string('name');
            $table->timestamps();

            // Same name allowed in different companies, but not twice in the same company
            $table->unique(['company_id', 'name']);
        });

        Schema::create('stock_categories', function (Blueprint $table) {
            $table->id();
            // companies.id is `int unsigned` (not the usual bigint), so this must match it
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // null = Primary (top level). A category with sub-categories can't be deleted, so no cascade here
            $table->foreignId('parent_id')->nullable()->constrained('stock_categories');
            $table->string('name');
            $table->timestamps();

            // Same name allowed in different companies, but not twice in the same company
            $table->unique(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_categories');
        Schema::dropIfExists('stock_groups');
    }
};
