<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('judul');
            $table->longText('bahan_analisis')->nullable(); // input UTS/UAS lama
            $table->json('hasil_prediksi'); // Analisis pola soal, topik ujian, soal potensial, readiness score
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_predictions');
    }
};
