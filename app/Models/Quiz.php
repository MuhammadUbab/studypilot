<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'judul_quiz',
        'total_soal',
        'skor',
        'soal_jawaban',
    ];

    protected $casts = [
        'soal_jawaban' => 'array',
        'skor' => 'integer',
        'total_soal' => 'integer',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
