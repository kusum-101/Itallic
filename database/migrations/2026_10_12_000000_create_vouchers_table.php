<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Account vouchers (Journal, Sales, Purchase, Receipt, Payment, Contra) and their document proofs.
     * All types share one table: each voucher moves one amount from a Credit ledger to a Debit ledger.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            // companies.id and users.id are `int unsigned` (not the usual bigint), so these must match
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            // journal, sales, purchase, receipt, payment, contra (VoucherController::TYPES)
            $table->string('type', 20);

            // Voucher number = prefix / financial year / serial, e.g. Jv/26-27/00001.
            // The serial starts again from 1 for every type in every financial year.
            $table->char('financial_year', 5);
            $table->unsignedInteger('serial');
            $table->string('voucher_no', 30);

            $table->date('date');

            // No onDelete: a ledger that is used in a voucher can't be deleted
            $table->foreignId('dr_ledger_id')->constrained('ledgers');
            $table->foreignId('cr_ledger_id')->constrained('ledgers');

            $table->enum('reference', ['New Ref', 'Against Ref', 'Advance', 'On Account']);
            $table->string('bill_no', 100)->nullable();
            $table->decimal('quote_amount', 15, 2)->nullable();
            $table->decimal('amount', 15, 2);
            // Quote Amount minus Amount (never below 0)
            $table->decimal('due_amount', 15, 2)->default(0);
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

        Schema::create('voucher_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            // Where the file sits on the private disk (storage/app/private), and the name it was uploaded with
            $table->string('path');
            $table->string('original_name');
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_documents');
        Schema::dropIfExists('vouchers');
    }
};
