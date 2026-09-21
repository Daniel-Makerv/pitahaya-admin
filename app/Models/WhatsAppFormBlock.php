<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppFormBlock extends Model
{
    protected $table = 'whats_app_form_blocks';

    protected $guarded = [];

    protected $casts = [
        'is_start' => 'boolean',
        'is_final' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(
            WhatsAppForm::class,
            'form_id'
        );
    }

    public function questions()
    {
        return $this->hasMany(
            WhatsAppQuestion::class,
            'block_id'
        )->where('active', true)
            ->orderBy('sort_order');
    }

    public function nextBlock()
    {
        return $this->belongsTo(
            WhatsAppFormBlock::class,
            'next_block_id'
        );
    }
}
