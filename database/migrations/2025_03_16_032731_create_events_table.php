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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('picture_event');
            $table->string('label_event');
            $table->text('description_event');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('localisation');
            $table->decimal('amount_event', 10, 2);
            $table->integer('number_available_event');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
