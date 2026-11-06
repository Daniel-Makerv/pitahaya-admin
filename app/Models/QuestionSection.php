<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSection extends Model
{
    protected $table = 'question_sections';

    protected $fillable = [
        'title',
        'description',
        'count_questions',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_section_id')->orderBy('sort_order');
    }
}
