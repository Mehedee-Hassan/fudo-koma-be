<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', fn (Blueprint $t) => $t->boolean('is_featured')->default(false));
        Schema::table('cart_updates', fn (Blueprint $t) => $t->string('type')->default('announcement'));
        Schema::table('cart_schedules', function (Blueprint $t) {

            $t->date('specific_date')->nullable();
            $t->boolean('is_active')->default(true);
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
        });
        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $t->string('target_type');
            $t->unsignedBigInteger('target_id');
            $t->string('reason');
            $t->text('note')->nullable();
            $t->string('status')->default('open')->index();
            $t->text('resolution_note')->nullable();
            $t->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::table('cart_schedules', fn (Blueprint $t) => $t->dropColumn(['specific_date', 'is_active', 'latitude', 'longitude']));
        Schema::table('cart_updates', fn (Blueprint $t) => $t->dropColumn('type'));
        Schema::table('carts', fn (Blueprint $t) => $t->dropColumn('is_featured'));
    }
};
