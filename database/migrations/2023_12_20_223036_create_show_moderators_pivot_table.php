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
        Schema::create('show_moderators', function (Blueprint $table) {
            $table->unsignedBigInteger('show_id');
            $table->unsignedBigInteger('moderator_id');
            $table->boolean('primary');
            $table->timestamps();
            $table->primary(['show_id', 'moderator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('show_moderators');
    }
};
