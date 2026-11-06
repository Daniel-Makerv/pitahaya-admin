<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionType extends Model
{
    protected $table = 'question_types';

    protected $fillable = [
        'name',
        'str',
    ];

    /**
     * Tipos que necesitan opciones (Selección única y Selección múltiple).
     */
    public function requiresOptions(): bool
    {
        return $this->str === 'select';
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_type_id');
    }
}
