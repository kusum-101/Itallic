<?php

use App\Models\Unit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Inventory Master: Units (Nos, Kg, ...) and Godowns (where the stock is kept).
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            // companies.id is `int unsigned` (not the usual bigint), so this must match it
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // Short name shown everywhere, e.g. Kg
            $table->string('symbol', 20);
            // Full name, e.g. Kilogram
            $table->string('name')->nullable();
            // How many digits after the point a quantity can have: Nos = 0, Kg = 3
            $table->unsignedTinyInteger('decimals')->default(0);
            $table->timestamps();

            // Same symbol allowed in different companies, but not twice in the same company
            $table->unique(['company_id', 'symbol']);
        });

        Schema::create('godowns', function (Blueprint $table) {
            $table->id();
            // companies.id is `int unsigned` (not the usual bigint), so this must match it
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('name');
            $table->text('address')->nullable();
            // The default "Main Location" godown: it can be renamed but not deleted
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Same godown name allowed in different companies, but not twice in the same company
            $table->unique(['company_id', 'name']);
        });

        // Companies made before this migration get the default units and godown too
        // (new companies get them in CredentialController::registerCompany).
        // Plain DB inserts, not Unit::seedDefaults(): later migrations add columns (e.g. uqc)
        // that seedDefaults fills, and those columns don't exist yet at this point.
        foreach (DB::table('companies')->pluck('id') as $companyId) {
            foreach (Unit::DEFAULTS as $symbol => [$name, $decimals]) {
                DB::table('units')->insert([
                    'company_id' => $companyId, 'symbol' => $symbol, 'name' => $name, 'decimals' => $decimals,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            DB::table('godowns')->insert([
                'company_id' => $companyId, 'name' => 'Main Location', 'is_primary' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('godowns');
        Schema::dropIfExists('units');
    }
};
