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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            // companies.id is `int unsigned` (not the usual bigint), so this must match it
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('groups')->onDelete('set null');
            $table->string('name');
            $table->enum('nature', ['Assets', 'Liabilities', 'Income', 'Expenses']);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Same group name allowed in different companies, but not twice in the same company
            $table->unique(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
