<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBaseOption extends Model
{
    protected $table = 'questions_base_options';

    protected $fillable = [
        'question_base_id',
        'text',
        'sort_order',
    ];

    public function questionBase(): BelongsTo
    {
        return $this->belongsTo(QuestionBase::class, 'question_base_id');
    }
}
