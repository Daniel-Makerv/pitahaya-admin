<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppQuestion extends Model
{
    protected $table = 'whats_app_questions';

    protected $guarded = [];

    protected $casts = [
        'required' => 'boolean',
        'active' => 'boolean',
    ];

    public function block()
    {
        return $this->belongsTo(
            WhatsAppFormBlock::class,
            'block_id'
        );
    }

    public function options()
    {
        return $this->hasMany(
            WhatsAppQuestionOption::class,
            'question_id'
        )->where('active', true)
            ->orderBy('sort_order');
    }
}
