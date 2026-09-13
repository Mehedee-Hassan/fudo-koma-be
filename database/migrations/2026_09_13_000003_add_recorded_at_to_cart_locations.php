<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_locations', function (Blueprint $table) {
            $table->timestamp('recorded_at')->nullable();
        });

        // Preserve the known age of existing positions; migration is not a GPS refresh.
        DB::table('cart_locations')->update([
            'recorded_at' => DB::raw('COALESCE(updated_at, created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('cart_locations', fn (Blueprint $table) => $table->dropColumn('recorded_at'));
    }
};
