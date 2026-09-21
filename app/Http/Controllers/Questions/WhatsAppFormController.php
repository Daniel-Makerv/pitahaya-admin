<?php

namespace App\Http\Controllers\Questions;

use App\Models\WhatsAppForm;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class WhatsAppFormController extends Controller
{
    public function index()
    {
        $forms = WhatsAppForm::query()
            ->withCount('blocks')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('WhatsApp/Forms/Index', [
            'forms' => $forms,
        ]);
    }

    public function create()
    {
        return Inertia::render('WhatsApp/Forms/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $form = WhatsAppForm::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'active' => true,
        ]);

        return redirect()
            ->route('whatsapp.forms.edit', $form)
            ->with('success', 'Formulario creado correctamente.');
    }

    public function edit(WhatsAppForm $form)
    {
        $form->load([
            'blocks.questions.options',
        ]);

        return Inertia::render('WhatsApp/Forms/Edit', [
            'form' => $form,
        ]);
    }

    public function update(Request $request, WhatsAppForm $form)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],
        ]);

        $form->update($data);

        return back()->with(
            'success',
            'Formulario actualizado correctamente.'
        );
    }

    public function destroy(WhatsAppForm $form)
    {
        $form->delete();

        return redirect()
            ->route('whatsapp.forms.index')
            ->with('success', 'Formulario eliminado correctamente.');
    }
}
