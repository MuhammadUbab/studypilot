<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('judul');
            $table->string('tipe_file'); // pdf, pptx, docx, audio, video, youtube
            $table->string('file_url')->nullable(); // link file di Supabase Storage / YouTube URL
            $table->longText('summary')->nullable(); // Ringkasan AI
            $table->longText('mindmap_data')->nullable(); // Data mindmap JSON/Mermaid
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
