<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'judul',
        'bahan_analisis',
        'hasil_prediksi',
    ];

    protected $casts = [
        'hasil_prediksi' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
