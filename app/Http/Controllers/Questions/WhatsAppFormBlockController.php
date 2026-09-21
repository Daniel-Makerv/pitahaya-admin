<?php

namespace App\Http\Controllers\Questions;

use App\Models\WhatsAppForm;
use App\Models\WhatsAppFormBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class WhatsAppFormBlockController extends Controller
{
    public function store(Request $request, WhatsAppForm $form)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_start' => ['required', 'boolean'],
            'is_final' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $form) {

            // Solo puede existir un bloque inicial
            if ($data['is_start']) {
                WhatsAppFormBlock::where('form_id', $form->id)
                    ->where('is_start', true)
                    ->update([
                        'is_start' => false,
                    ]);
            }

            $lastOrder = WhatsAppFormBlock::where(
                'form_id',
                $form->id
            )->max('sort_order');

            WhatsAppFormBlock::create([
                'form_id' => $form->id,
                'name' => $data['name'],
                'sort_order' => ($lastOrder ?? 0) + 1,
                'is_start' => $data['is_start'],
                'is_final' => $data['is_final'],
            ]);
        });

        return back()->with(
            'success',
            'Bloque creado correctamente.'
        );
    }
}
