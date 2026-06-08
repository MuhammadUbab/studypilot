<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->string('judul_quiz');
            $table->integer('total_soal');
            $table->integer('skor')->nullable(); // Skor pengerjaan terakhir
            $table->json('soal_jawaban'); // Data JSON untuk pertanyaan, pilihan, dan pembahasan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
