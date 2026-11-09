<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pregunta reutilizable (banco de preguntas).
 * Se copia a una pregunta principal desde "Desde preguntas generales".
 */
class QuestionBase extends Model
{
    protected $table = 'questions_base';

    protected $fillable = [
        'question_type_id',
        'text',
        'required',
    ];

    protected $casts = [
        'required' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionBaseOption::class, 'question_base_id')->orderBy('sort_order');
    }

    /**
     * Copias de esta pregunta dentro de preguntas principales.
     */
    public function copies(): HasMany
    {
        return $this->hasMany(Question::class, 'question_base_id');
    }
}
