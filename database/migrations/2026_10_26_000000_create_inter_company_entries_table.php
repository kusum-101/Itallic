<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * A Sales voucher whose customer is another company on this software. The buyer company
     * sees it under Incoming Purchases and, with one OK, gets it as a Purchase voucher.
     */
    public function up(): void
    {
        Schema::create('inter_company_entries', function (Blueprint $table) {
            $table->id();

            // The seller's Sales voucher (one entry per sale). Deleting the sale removes the entry.
            $table->foreignId('sale_voucher_id')->unique()->constrained('vouchers')->onDelete('cascade');

            // companies.id is `int unsigned`, so these must match
            $table->unsignedInteger('from_company_id');
            $table->foreign('from_company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedInteger('to_company_id');
            $table->foreign('to_company_id')->references('id')->on('companies')->onDelete('cascade');

            // Pending: waiting for the buyer's OK. Accepted: booked as a Purchase. Rejected: the buyer said no.
            // An edited sale goes back to Pending, so the buyer's Purchase is updated on the next OK.
            $table->enum('status', ['Pending', 'Accepted', 'Rejected'])->default('Pending');

            // The buyer's Purchase voucher made from this sale
            $table->foreignId('purchase_voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();

            // Who accepted / rejected it, and when
            $table->unsignedInteger('handled_by')->nullable();
            $table->foreign('handled_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();

            // Incoming Purchases lists the Pending entries of one company
            $table->index(['to_company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inter_company_entries');
    }
};
