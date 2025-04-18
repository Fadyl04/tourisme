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
        Schema::create('paiement_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_paiement')->constrained('paiements')->onDelete('cascade');
            $table->string('label', 255);
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['mobile_money', 'bank_card']);
            $table->enum('status', ['pending', 'paid', 'failure'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiement_histories');
    }
};
