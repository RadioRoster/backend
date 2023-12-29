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
        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->dateTime('start_date')->index()->comment('The date the show starts');
            $table->dateTime('end_date')->index()->comment('The date the show ends');
            $table->boolean('is_live');
            $table->boolean('enabled');
            $table->unsignedBigInteger('locked_by')->nullable()->comment('The user who locked the show');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shows');
    }
};
