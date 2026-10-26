<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * The seller can move an accepted sale to another customer. The first buyer's entry then becomes
     * Withdrawn (they still have their voucher, and are told), and the new buyer gets a new entry.
     * So one sale can now have more than one entry, only one of them not Withdrawn.
     */
    public function up(): void
    {
        Schema::table('inter_company_entries', function (Blueprint $table) {
            // The foreign key needs an index, so add the plain one before dropping the unique one
            $table->index('sale_voucher_id');
            $table->dropUnique(['sale_voucher_id']);

            $table->enum('status', ['Pending', 'Accepted', 'Rejected', 'Withdrawn'])->default('Pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inter_company_entries', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Accepted', 'Rejected'])->default('Pending')->change();

            $table->unique('sale_voucher_id');
            $table->dropIndex(['sale_voucher_id']);
        });
    }
};
