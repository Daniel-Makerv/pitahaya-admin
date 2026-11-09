<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Pregunta secundaria (hija) con su tipo y opciones.
 *
 * @mixin \App\Models\Question
 */
class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'order' => $this->sort_order,
            'text'  => $this->text,
            'required' => (bool) ($this->required ?? true),
            'type'  => $this->whenLoaded('type', fn () => $this->type ? [
                'id'   => $this->type->id,
                'name' => $this->type->name,
                'str'  => $this->type->str,
            ] : null),
            'options' => $this->whenLoaded('options', fn () => $this->options->map(fn ($option) => [
                'id'    => $option->id,
                'order' => $option->sort_order,
                'text'  => $option->text,
            ])->values()),
        ];
    }
}
