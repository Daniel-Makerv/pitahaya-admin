<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionSection extends Model
{
    protected $fillable = [
        'title',
        'description',
        'count_questions',
    ];
}
