<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppQuestionOption extends Model
{
    protected $table = 'whats_app_question_options';

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'score' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(
            WhatsAppQuestion::class,
            'question_id'
        );
    }

    public function nextBlock()
    {
        return $this->belongsTo(
            WhatsAppFormBlock::class,
            'next_block_id'
        );
    }
}
