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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('name');
            $table->string('bearbeiter');
            $table->decimal('threshold_warning', 10, 2)->default(3.0);
            $table->decimal('threshold_caution', 10, 2)->default(5.0);
            $table->decimal('threshold_alarm', 10, 2)->default(10.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
