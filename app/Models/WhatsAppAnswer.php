<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppAnswer extends Model
{
    protected $table = 'whats_app_answers';

    protected $guarded = [];

    protected $casts = [
        'score' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(
            WhatsAppFormSession::class,
            'form_session_id'
        );
    }

    public function question()
    {
        return $this->belongsTo(
            WhatsAppQuestion::class,
            'question_id'
        );
    }

    public function option()
    {
        return $this->belongsTo(
            WhatsAppQuestionOption::class,
            'option_id'
        );
    }
}
