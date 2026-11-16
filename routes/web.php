<?php

use App\Exports\FormsExport;
use App\Exports\FormsExportV2Template;
use App\Exports\FormsMultiSheetExport;
use App\Http\Controllers\{Questions, RaffleController, RaffleInvitationController};
use App\Http\Controllers\Questions\QuestionController;
use App\Models\Form;
use App\Models\TypeForm;
use App\Support\FormFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

/* =========================================================
 |  Públicas (sin sesión)
 * ========================================================= */

Route::get('/', fn() => redirect('dashboard'))->name('home');

Route::get('/privacy-policy', fn() => view('privacy-policy'));

// Registro + rifa: página a la que llegan desde el correo de invitación
Route::get('rifa/invitacion/{token}', [RaffleInvitationController::class, 'show'])
    ->name('raffles.invitation.show');

Route::post('rifa/invitacion/{token}/progreso', [RaffleInvitationController::class, 'progress'])
    ->middleware('throttle:60,1')
    ->name('raffles.invitation.progress');

Route::post('rifa/invitacion/{token}', [RaffleInvitationController::class, 'confirm'])
    ->middleware('throttle:10,1')
    ->name('raffles.invitation.confirm');

/* =========================================================
 |  Admin (requiere sesión)
 * ========================================================= */

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    /* ---------- Formularios contestados ---------- */

    Route::get('forms', function (Request $request) {
        $search = $request->input('search');
        $typeId = $request->input('type_form_id');

        // Por default: del día de hoy hace un año a hoy
        ['from' => $from, 'to' => $to, 'range' => $range] = FormFilters::dates($request);

        $formsQuery = Form::leftJoin('type_forms', 'forms.type_form_id', '=', 'type_forms.id')
            ->select('forms.*', 'type_forms.name as type', 'type_forms.str as str')
            ->orderByDesc('forms.id');

        FormFilters::apply($formsQuery, $search, $typeId, $from, $to);

        $forms = $formsQuery->paginate(12)->withQueryString(); // mantiene los filtros en la paginación

        // Contadores por área: respetan búsqueda y rango de fechas (no el área seleccionada)
        $counts = TypeForm::orderBy('name')->get()->map(function ($typeForm) use ($search, $from, $to) {
            $query = Form::leftJoin('type_forms', 'forms.type_form_id', '=', 'type_forms.id');

            return [
                'id'    => $typeForm->id,
                'name'  => $typeForm->name,
                'total' => FormFilters::apply($query, $search, $typeForm->id, $from, $to)->count(),
            ];
        });

        return Inertia::render('Forms', [
            'forms'   => $forms,
            'counts'  => $counts,
            // Rifas abiertas para el botón "Invitar a rifa"
            'raffles' => \App\Models\Raffle::where('status', 'open')->latest()->get(['id', 'name', 'prize']),
            'filters' => [
                'search'       => $search,
                'type_form_id' => $typeId ? (int) $typeId : null,
                'from'         => $from,
                'to'           => $to,
                'range'        => $range,
            ],
        ]);
    })->name('forms');

    Route::get('forms/{form}/view', [Questions\FormViewController::class, 'show'])->whereNumber('form')->name('forms.view');
    Route::get('forms/{form}/pdf', [Questions\FormViewController::class, 'pdf'])->whereNumber('form')->name('forms.pdf');
    Route::delete('forms/{form}', [Questions\FormViewController::class, 'destroy'])->whereNumber('form')->name('forms.destroy');

    Route::get('/forms/export', function (Request $request) {
        $search = $request->input('search');
        $typeId = $request->input('type_form_id');

        // Mismo default que la lista: último año
        ['from' => $from, 'to' => $to] = FormFilters::dates($request);

        // Nombre del archivo con el rango: formularios_completos_2026-11-01_a_2026-11-12.xlsx
        [$start, $end] = FormFilters::range($from, $to);
        $period = match (true) {
            $start && $end => $start->format('Y-m-d') . '_a_' . $end->format('Y-m-d'),
            (bool) $start  => 'desde_' . $start->format('Y-m-d'),
            (bool) $end    => 'hasta_' . $end->format('Y-m-d'),
            default        => 'todo_' . now()->format('Y-m-d'),
        };

        // "Todos": una hoja por área de interés
        if (! $typeId) {
            return Excel::download(
                new FormsMultiSheetExport($search, $from, $to),
                "formularios_completos_{$period}.xlsx"
            );
        }

        $typeName = TypeForm::whereKey($typeId)->value('name') ?? 'formularios';
        $forms = FormsExport::query($search, $typeId, $from, $to)->get();

        return Excel::download(
            new FormsExport($forms, $typeId),
            'formularios_' . Str::slug($typeName) . '_' . $period . '.xlsx'
        );
    })->name('forms.export');

    Route::get('/forms/export-v2', fn() => Excel::download(new FormsExportV2Template(), 'formularios_v2.xlsx'))
        ->name('forms.export.v2');

    /* ---------- Preguntas ---------- */

    // Las de "general" van antes que las de {section}
    Route::post('questions/general', [QuestionController::class, 'storeGeneral'])->name('questions.general.store');
    Route::delete('questions/general/{questionBase}', [QuestionController::class, 'destroyGeneral'])->name('questions.general.destroy');

    Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('questions/{id}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('questions/{section}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{section}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    Route::post('questions/{section}/import', [QuestionController::class, 'importQuestions'])->name('questions.import');
    Route::post('questions/{section}/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
    Route::post('questions/{section}/questions', [QuestionController::class, 'storeQuestion'])->name('questions.store-question');
    Route::put('questions/{section}/questions/{question}', [QuestionController::class, 'updateQuestion'])->name('questions.update-question');
    Route::delete('questions/{section}/questions/{question}', [QuestionController::class, 'destroyQuestion'])->name('questions.destroy-question');

    /* ---------- WhatsApp ---------- */

    Route::get('/whatsapp/settings', fn() => Inertia::render('WhatsApp/Settings/Index', [
        'metaAppId' => config('services.whatsapp.app_id'),
        'configId'  => config('services.whatsapp.embedded_signup_config_id'),
    ]))->name('whatsapp.settings');

    Route::prefix('whatsapp/forms')->name('whatsapp.forms.')->group(function () {
        Route::get('/', [Questions\WhatsAppFormController::class, 'index'])->name('index');
        Route::get('/create', [Questions\WhatsAppFormController::class, 'create'])->name('create');
        Route::post('/', [Questions\WhatsAppFormController::class, 'store'])->name('store');
        Route::get('/{form}/edit', [Questions\WhatsAppFormController::class, 'edit'])->name('edit');
        Route::put('/{form}', [Questions\WhatsAppFormController::class, 'update'])->name('update');
        Route::delete('/{form}', [Questions\WhatsAppFormController::class, 'destroy'])->name('destroy');

        // Bloques y preguntas
        Route::post('/{form}/blocks', [Questions\WhatsAppFormBlockController::class, 'store'])->name('blocks.store');
        Route::post('/{form}/blocks/{block}/questions', [Questions\WhatsAppQuestionController::class, 'store'])->name('questions.store');
        Route::patch('/questions/{question}/move', [Questions\WhatsAppQuestionController::class, 'move'])->name('questions.move');
        Route::patch('/blocks/{block}/questions/reorder', [Questions\WhatsAppQuestionController::class, 'reorder'])->name('questions.reorder');
    });

    Route::put('/whatsapp/questions/{question}', [Questions\WhatsAppQuestionController::class, 'update'])->name('whatsapp.questions.update');
    Route::delete('/whatsapp/questions/{question}', [Questions\WhatsAppQuestionController::class, 'destroy'])->name('whatsapp.questions.destroy');

    // Opciones de preguntas
    Route::post('/whatsapp/questions/{question}/options', [Questions\WhatsAppQuestionOptionController::class, 'store'])->name('whatsapp.questions.options.store');
    Route::delete('/whatsapp/question-options/{option}', [Questions\WhatsAppQuestionOptionController::class, 'destroy'])->name('whatsapp.questions.options.destroy');

    // Bloques
    Route::delete('/whatsapp/blocks/{block}', [Questions\WhatsAppFormController::class, 'destroyBlock'])->name('whatsapp.blocks.destroy');

    /* ---------- Rifas ---------- */

    Route::get('raffles', [RaffleController::class, 'index'])->name('raffles.index');
    Route::post('raffles', [RaffleController::class, 'store'])->name('raffles.store');
    Route::get('raffles/{raffle}', [RaffleController::class, 'show'])->whereNumber('raffle')->name('raffles.show');
    Route::put('raffles/{raffle}', [RaffleController::class, 'update'])->whereNumber('raffle')->name('raffles.update');
    Route::delete('raffles/{raffle}', [RaffleController::class, 'destroy'])->whereNumber('raffle')->name('raffles.destroy');

    // Invitaciones
    Route::get('raffles/{raffle}/candidates', [RaffleController::class, 'candidates'])->name('raffles.candidates');
    Route::post('raffles/{raffle}/invite', [RaffleController::class, 'invite'])->name('raffles.invite');
    Route::post('raffles/{raffle}/invite-manual', [RaffleController::class, 'inviteManual'])->name('raffles.invite-manual');
    Route::post('raffles/{raffle}/participants/{participant}/resend', [RaffleController::class, 'resend'])->name('raffles.resend');
    Route::delete('raffles/{raffle}/participants/{participant}', [RaffleController::class, 'removeParticipant'])->name('raffles.participants.destroy');

    // Sorteo
    Route::post('raffles/{raffle}/draw', [RaffleController::class, 'draw'])->name('raffles.draw');
});

// Temario de PITAMEX (botón "Descargar temario" del correo)
Route::get('temario', function () {
    $path = public_path('docs/Temario-PITAMEX.pdf');

    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Content-Type'        => 'application/pdf',
        // "inline": se abre en el navegador; desde ahí se puede descargar con este nombre
        'Content-Disposition' => 'inline; filename="Temario-PITAMEX.pdf"',
    ]);
})->name('temario');


/* ---------- Página pública de la invitación (sin sesión) ---------- */
Route::get('rifa/invitacion/{token}', [RaffleInvitationController::class, 'show'])
    ->name('raffles.invitation.show');

Route::post('rifa/invitacion/{token}/progreso', [RaffleInvitationController::class, 'progress'])
    ->middleware('throttle:60,1')
    ->name('raffles.invitation.progress');

Route::post('rifa/invitacion/{token}', [RaffleInvitationController::class, 'confirm'])
    ->middleware('throttle:10,1')
    ->name('raffles.invitation.confirm');

/* ---------- Registro sin correo: link público de cada rifa ---------- */
Route::get('rifa/{code}', [RaffleInvitationController::class, 'openShow'])
    ->where('code', '[A-Za-z0-9]{6,16}')
    ->name('raffles.open.show');

Route::post('rifa/{code}/registro', [RaffleInvitationController::class, 'openStart'])
    ->where('code', '[A-Za-z0-9]{6,16}')
    ->middleware('throttle:20,1')
    ->name('raffles.open.start');

require __DIR__ . '/settings.php';
