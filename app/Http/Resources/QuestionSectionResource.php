<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Pregunta principal (padre) con sus preguntas secundarias (hijas).
 *
 * @mixin \App\Models\QuestionSection
 */
class QuestionSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'total_questions' => $this->whenLoaded('questions', fn() => $this->questions->count()),
            'questions'       => QuestionResource::collection($this->whenLoaded('questions')),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
