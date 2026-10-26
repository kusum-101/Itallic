<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * When the seller deletes a sale the buyer already saved, the buyer's entry stays (as Withdrawn)
     * so they are told and can delete their voucher. The entry then has no sale any more,
     * so it keeps the sale's number itself.
     */
    public function up(): void
    {
        Schema::table('inter_company_entries', function (Blueprint $table) {
            // e.g. Sl/26-27/00001, the same as the sale's voucher_no
            $table->string('sale_no', 30)->nullable()->after('sale_voucher_id');
        });

        DB::statement('UPDATE inter_company_entries e JOIN vouchers v ON v.id = e.sale_voucher_id SET e.sale_no = v.voucher_no');

        Schema::table('inter_company_entries', function (Blueprint $table) {
            // Deleting the sale now leaves the entry (with no sale) instead of deleting it.
            // InterCompanyEntry::saleDeleted removes the entries nobody saved a voucher for.
            $table->dropForeign(['sale_voucher_id']);
            $table->unsignedBigInteger('sale_voucher_id')->nullable()->change();
            $table->foreign('sale_voucher_id')->references('id')->on('vouchers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Entries of deleted sales can't keep a sale they don't have
        DB::table('inter_company_entries')->whereNull('sale_voucher_id')->delete();

        Schema::table('inter_company_entries', function (Blueprint $table) {
            $table->dropForeign(['sale_voucher_id']);
            $table->unsignedBigInteger('sale_voucher_id')->nullable(false)->change();
            $table->foreign('sale_voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->dropColumn('sale_no');
        });
    }
};
