<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Inventory vouchers (Material Inward now; Outward, Godown Transfer, Physical Stock later):
     * a voucher (stock_vouchers) with one row per item (stock_voucher_lines).
     * Every row says how much of an item came into / went out of a godown, so an item's stock is
     *   opening stock + all quantity_in - all quantity_out   (per godown)
     */
    public function up(): void
    {
        Schema::create('stock_vouchers', function (Blueprint $table) {
            $table->id();
            // companies.id and users.id are `int unsigned` (not the usual bigint), so these must match
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            // material-inward, ... (StockVoucherController::TYPES)
            $table->string('type', 20);

            // Voucher number = prefix / financial year / serial, e.g. MIN/26-27/00001.
            // The serial starts again from 1 for every type in every financial year.
            $table->char('financial_year', 5);
            $table->unsignedInteger('serial');
            $table->string('voucher_no', 30);

            $table->date('date');

            // The supplier / customer the material came from (optional).
            // No onDelete: a ledger used in a voucher can't be deleted
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers');
            // Their challan / bill number
            $table->string('reference_no', 100)->nullable();
            $table->text('narration')->nullable();

            // A verified voucher is locked: it can't be edited or deleted until it is unverified
            $table->enum('status', ['Not Verified', 'Verified'])->default('Not Verified');
            $table->unsignedInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            // Two vouchers can never get the same number
            $table->unique(['company_id', 'type', 'financial_year', 'serial']);
            // The register lists one type of one company, newest date first
            $table->index(['company_id', 'type', 'date']);
        });

        Schema::create('stock_voucher_lines', function (Blueprint $table) {
            $table->id();
            // Rows go with their voucher
            $table->foreignId('stock_voucher_id')->constrained('stock_vouchers')->onDelete('cascade');
            // No onDelete: an item / godown used in a voucher can't be deleted
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('godown_id')->constrained('godowns');

            // In the unit the item is counted in (the second unit of a compound unit).
            // A row has one of the two: Material Inward fills quantity_in
            $table->decimal('quantity_in', 15, 4)->default(0);
            $table->decimal('quantity_out', 15, 4)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            // Quantity x Rate, worked out when saving
            $table->decimal('value', 15, 2)->default(0);
            $table->timestamps();

            // Stock of an item in a godown adds up these rows
            $table->index(['item_id', 'godown_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_voucher_lines');
        Schema::dropIfExists('stock_vouchers');
    }
};
