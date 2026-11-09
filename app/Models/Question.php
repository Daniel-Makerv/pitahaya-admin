<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $table = 'questions';

    protected $fillable = [
        'question_section_id',
        'question_type_id',
        'question_base_id',
        'text',
        'required',
        'sort_order',
    ];

    protected $casts = [
        'required' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(QuestionSection::class, 'question_section_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id')->orderBy('sort_order');
    }

    /**
     * Pregunta base de la que se copió (si aplica).
     */
    public function base(): BelongsTo
    {
        return $this->belongsTo(QuestionBase::class, 'question_base_id');
    }
}
