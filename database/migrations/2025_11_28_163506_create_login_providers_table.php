<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('provider_name');
            $table->string('provider_id');
            $table->string('nick_name');
            $table->string('email');
            $table->text('avatar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_providers');
    }
};
