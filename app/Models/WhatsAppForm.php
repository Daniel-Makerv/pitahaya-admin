<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppForm extends Model
{
    protected $table = 'whats_app_forms';

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function blocks()
    {
        return $this->hasMany(
            WhatsAppFormBlock::class,
            'form_id'
        )->orderBy('sort_order');
    }

    public function startBlock()
    {
        return $this->hasOne(
            WhatsAppFormBlock::class,
            'form_id'
        )->where('is_start', true);
    }

    public function sessions()
    {
        return $this->hasMany(
            WhatsAppFormSession::class,
            'form_id'
        );
    }
}
