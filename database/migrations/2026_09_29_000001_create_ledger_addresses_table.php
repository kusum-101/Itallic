<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Billing and shipping addresses of a client (Sundry Debtors ledger).
     */
    public function up(): void
    {
        Schema::create('ledger_addresses', function (Blueprint $table) {
            $table->id();
            // companies.id is `int unsigned` (not the usual bigint), so this must match it
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('cascade');
            $table->enum('type', ['Billing', 'Shipping']);
            $table->text('address');
            $table->unsignedInteger('state_id');
            $table->foreign('state_id')->references('id')->on('states');
            $table->unsignedInteger('district_id');
            $table->foreign('district_id')->references('id')->on('districts');
            // Typed or picked from the district's suggestions (the list doesn't have every town, e.g. Noida)
            $table->string('city', 100);
            $table->char('pin_code', 6);
            $table->timestamps();

            // Lists and the quotation form look up a client's addresses inside one company
            $table->index(['company_id', 'ledger_id']);
        });

        // Addresses now live in ledger_addresses, so the old single address column goes
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->text('address')->nullable()->after('name');
        });

        Schema::dropIfExists('ledger_addresses');
    }
};
