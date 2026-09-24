<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_merchant_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->unique();
            $table->string('offer_id', 128)->unique();
            $table->string('google_product_name', 512)->nullable();
            $table->string('data_source_name', 255)->nullable();
            $table->string('sync_status', 32)->default('pending')->index();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->json('last_issues')->nullable();
            $table->string('operation', 32)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_merchant_products');
    }
};
