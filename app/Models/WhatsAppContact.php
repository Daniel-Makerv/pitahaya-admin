<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppContact extends Model
{
    protected $table = 'whats_app_contacts';

    protected $guarded = [];

    public function conversations()
    {
        return $this->hasMany(WhatsAppConversation::class);
    }

}
