<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * A sale reaches another company by the customer's GST No. (Incoming Vouchers),
     * so two companies can't have the same one. Companies without a GST No. (null) are fine.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unique('gst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['gst']);
        });
    }
};
