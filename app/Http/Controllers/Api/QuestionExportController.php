<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionSectionResource;
use App\Models\QuestionSection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuestionExportController extends Controller
{
    /**
     * GET /api/questions
     * Todas las preguntas principales con sus preguntas secundarias y opciones.
     */
    public function index()
    {
        $sections = QuestionSection::with(['questions.type', 'questions.options'])
            ->orderBy('id')
            ->get();

        // Sin envoltorio "data": el JSON es directamente la lista
        return response()->json(
            QuestionSectionResource::collection($sections)->resolve()
        );
    }

    /**
     * GET /api/questions/{section}
     * Una pregunta principal con sus preguntas secundarias y opciones.
     */
    public function show(QuestionSection $section): QuestionSectionResource
    {
        $section->load(['questions.type', 'questions.options']);

        return new QuestionSectionResource($section);
    }
}
