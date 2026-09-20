<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppConversation extends Model
{
    protected $table = 'whats_app_conversations';

    protected $guarded = [];

     protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(
            WhatsAppContact::class,
            'whatsapp_contact_id'
        );
    }

    public function messages()
    {
        return $this->hasMany(
            WhatsAppMessage::class,
            'whatsapp_conversation_id'
        );
    }
}
