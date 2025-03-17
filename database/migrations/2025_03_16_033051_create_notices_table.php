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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('id_site')->nullable()->constrained('sites')->onDelete('set null');
            $table->foreignId('id_event')->nullable()->constrained('events')->onDelete('set null');
            $table->integer('note')->checkBetween(1, 5);
            $table->text('comment');
            $table->timestamp('notice_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
