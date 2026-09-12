<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('customer')->index();
            $t->boolean('is_active')->default(true);
        });
        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->morphs('tokenable');
            $t->text('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable()->index();
            $t->timestamps();
        });
        Schema::create('carts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->string('cuisine')->nullable();
            $t->string('status')->default('closed');
            $t->string('moderation_status')->default('pending')->index();
            $t->timestamps();
        });
        Schema::create('cart_locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cart_id')->unique()->constrained()->cascadeOnDelete();
            $t->decimal('latitude', 10, 7);
            $t->decimal('longitude', 10, 7);
            $t->string('address')->nullable();
            $t->timestamps();
            $t->index('updated_at');
        });
        Schema::create('user_locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->decimal('latitude', 10, 7);
            $t->decimal('longitude', 10, 7);
            $t->timestamps();
            $t->index('updated_at');
        });
        Schema::create('user_settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->boolean('push_enabled')->default(true);
            $t->boolean('nearby_enabled')->default(true);
            $t->boolean('updates_enabled')->default(true);
            $t->unsignedInteger('radius_meters')->default(5000);
            $t->timestamps();
        });
        Schema::create('follows', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $t->boolean('is_following')->default(true);
            $t->timestamps();
            $t->unique(['user_id', 'cart_id']);
        });
        Schema::create('cart_updates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('body');
            $t->timestamp('fanout_at')->nullable()->index();
            $t->timestamps();
        });
        Schema::create('cart_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('day_of_week');
            $t->time('opens_at');
            $t->time('closes_at');
            $t->string('timezone')->default('Asia/Tokyo');
            $t->string('address')->nullable();
            $t->timestamps();
            $t->index(['cart_id', 'day_of_week']);
        });
        Schema::create('photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->timestamps();
        });
        Schema::create('device_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('token');
            $t->string('token_hash', 64)->unique();
            $t->string('platform');
            $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('cart_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('dedupe_key')->unique();
            $t->string('type');
            $t->string('title');
            $t->text('body');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
        Schema::create('push_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $t->foreignId('device_token_id')->constrained()->cascadeOnDelete();
            $t->string('status')->default('pending')->index();
            $t->unsignedInteger('attempts')->default(0);
            $t->timestamp('available_at')->nullable()->index();
            $t->text('last_error')->nullable();
            $t->timestamps();
            $t->unique(['notification_id', 'device_token_id']);
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'push_deliveries', 'notifications', 'device_tokens', 'photos', 'cart_schedules', 'cart_updates', 'follows', 'user_settings', 'user_locations', 'cart_locations', 'carts', 'personal_access_tokens'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'is_active']));
    }
};
