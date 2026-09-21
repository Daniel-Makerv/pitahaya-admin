<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppFormSession extends Model
{
    protected $table = 'whats_app_form_sessions';

    protected $guarded = [];

    protected $casts = [
        'score' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(
            WhatsAppConversation::class,
            'whatsapp_conversation_id'
        );
    }

    public function form()
    {
        return $this->belongsTo(
            WhatsAppForm::class,
            'form_id'
        );
    }

    public function currentBlock()
    {
        return $this->belongsTo(
            WhatsAppFormBlock::class,
            'current_block_id'
        );
    }

    public function currentQuestion()
    {
        return $this->belongsTo(
            WhatsAppQuestion::class,
            'current_question_id'
        );
    }

    public function answers()
    {
        return $this->hasMany(
            WhatsAppAnswer::class,
            'form_session_id'
        );
    }
}
