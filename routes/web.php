<?php

use App\Models\Form;
use App\Models\TypeForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormsExport;
use App\Exports\FormsExportV2Template;
use App\Exports\FormsMultiSheetExport;
use App\Http\Controllers\Questions\QuestionController;
use App\Http\Controllers\{Questions};
use App\Http\Controllers\FormViewController;


Route::get('/', function () {
    return redirect('dashboard');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('forms/{form}/view', [Questions\FormViewController::class, 'show'])->whereNumber('form')->name('forms.view');
    Route::get('forms/{form}/pdf', [Questions\FormViewController::class, 'pdf'])->whereNumber('form')->name('forms.pdf');
});
Route::delete('forms/{form}', [Questions\FormViewController::class, 'destroy'])->whereNumber('form')->name('forms.destroy');

Route::get('forms', function (Request $request) {
    $search = $request->input('search');
    $typeId = $request->input('type_form_id');

    $formsQuery = Form::join('type_forms', 'forms.type_form_id', '=', 'type_forms.id')
        ->select('forms.*', 'type_forms.name as type', 'type_forms.str as str');

    // 🧭 Filtro por tipo de formulario
    if ($typeId) {
        $formsQuery->where('forms.type_form_id', $typeId);
    }
    // 👇 Filtro por búsqueda
    if ($search) {
        $formsQuery->where(function ($q) use ($search) {
            $q->where('forms.name', 'like', "%{$search}%")
                ->orWhere('forms.form->phone_contact', 'like', "%{$search}%") // 👈 busca dentro del JSON
                ->orWhere('type_forms.name', 'like', "%{$search}%"); // área de interés
        });
    }

    $forms = $formsQuery->paginate(12)->withQueryString(); // <- mantiene search en los links

    $counts = TypeForm::get()->map(function ($typeForm) {
        $total = Form::whereTypeFormId($typeForm->id)->count();

        return [
            'id' => $typeForm->id,
            'name' => $typeForm->name,
            'total' => $total,
        ];
    });

    return Inertia::render('Forms', [
        'forms' => $forms,
        'counts' => $counts,
        // 👇 Para rellenar el input en el front
        'filters' => [
            'search' => $search,
        ],
    ]);
})->middleware(['auth', 'verified'])->name('forms');

Route::get('/forms/export', function (Request $request) {
    $search = $request->input('search');
    $typeId = $request->input('type_form_id');


    if ($typeId == null || $typeId == "") {
        return Excel::download(new FormsMultiSheetExport($search), 'formularios_completos.xlsx');
    }


    $formsQuery = Form::join('type_forms', 'forms.type_form_id', '=', 'type_forms.id')
        ->select('forms.*', 'type_forms.name as type', 'type_forms.str as str');

    if ($search) {
        $formsQuery->where(function ($q) use ($search) {
            $q->where('forms.name', 'like', "%{$search}%")
                ->orWhere('type_forms.name', 'like', "%{$search}%")
                ->orWhere('forms.form->phone_contact', 'like', "%{$search}%");
        });
    }

    if ($typeId) {
        $formsQuery->where('forms.type_form_id', $typeId);
    }

    $forms = $formsQuery->get();

    // 🔹 Si usas maatwebsite/excel
    return Excel::download(new FormsExport($forms, $typeId), 'formularios.xlsx');
})->name('forms.export');


Route::get('/forms/export-v2', function (Request $request) {

    return Excel::download(new FormsExportV2Template(), 'formularios_v2.xlsx');
})->middleware(['auth', 'verified'])->name('forms.export.v2');

require __DIR__ . '/settings.php';



// Route::resource('questions', QuestionController::class);


// Route::get('questions', [QuestionController::class, 'index']);
// Route::get('questions', [QuestionController::class, 'index']);

Route::post('questions/general', [QuestionController::class, 'storeGeneral'])->name('questions.general.store');
Route::delete('questions/general/{questionBase}', [QuestionController::class, 'destroyGeneral'])->name('questions.general.destroy');
Route::post('questions/{section}/import', [QuestionController::class, 'importQuestions'])->name('questions.import');

Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
Route::delete('questions/{section}', [QuestionController::class, 'destroy'])->name('questions.destroy');
Route::get('questions/{id}/edit', [QuestionController::class, 'edit'])->name('questions.edit');

Route::put('questions/{section}', [QuestionController::class, 'update'])->name('questions.update');
Route::put('questions/{section}/questions/{question}', [QuestionController::class, 'updateQuestion'])->name('questions.update-question');
Route::post('questions/{section}/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');

Route::post('questions/{section}/questions', [QuestionController::class, 'storeQuestion'])->name('questions.store-question');
Route::delete('questions/{section}/questions/{question}', [QuestionController::class, 'destroyQuestion'])->name('questions.destroy-question');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
});



Route::middleware('auth')->group(function () {
    Route::get('/whatsapp/settings', function () {
        return Inertia::render('WhatsApp/Settings/Index', [
            'metaAppId' => config('services.whatsapp.app_id'),
            'configId' => config(
                'services.whatsapp.embedded_signup_config_id'
            ),
        ]);
    })->name('whatsapp.settings');
});


Route::prefix('whatsapp/forms')
    ->name('whatsapp.forms.')
    ->group(function () {

        Route::get('/', [Questions\WhatsAppFormController::class, 'index'])
            ->name('index');

        Route::get('/create', [Questions\WhatsAppFormController::class, 'create'])
            ->name('create');

        Route::post('/', [Questions\WhatsAppFormController::class, 'store'])
            ->name('store');

        Route::get('/{form}/edit', [Questions\WhatsAppFormController::class, 'edit'])
            ->name('edit');


        Route::put('/{form}', [Questions\WhatsAppFormController::class, 'update'])
            ->name('update');

        Route::delete('/{form}', [Questions\WhatsAppFormController::class, 'destroy'])
            ->name('destroy');


        // BLOQUES
        Route::post(
            '/{form}/blocks',
            [Questions\WhatsAppFormBlockController::class, 'store']
        )->name('blocks.store');

        Route::post(
            '/{form}/blocks/{block}/questions',
            [Questions\WhatsAppQuestionController::class, 'store']
        )->name('questions.store');


        Route::patch(
            '/questions/{question}/move',
            [Questions\WhatsAppQuestionController::class, 'move']
        )->name('questions.move');


        Route::patch(
            '/blocks/{block}/questions/reorder',
            [Questions\WhatsAppQuestionController::class, 'reorder']
        )->name('questions.reorder');
    });


Route::put(
    '/whatsapp/questions/{question}',
    [Questions\WhatsAppQuestionController::class, 'update']
)->name('whatsapp.questions.update');

Route::delete(
    '/whatsapp/questions/{question}',
    [Questions\WhatsAppQuestionController::class, 'destroy']
)->name('whatsapp.questions.destroy');

// Agregar opciones a preguntas existentes
Route::post(
    '/whatsapp/questions/{question}/options',
    [Questions\WhatsAppQuestionOptionController::class, 'store']
)->name('whatsapp.questions.options.store');

Route::delete(
    '/whatsapp/question-options/{option}',
    [Questions\WhatsAppQuestionOptionController::class, 'destroy']
)->name('whatsapp.questions.options.destroy');



//bloques
Route::delete(
    '/whatsapp/blocks/{block}',
    [Questions\WhatsAppFormController::class, 'destroyBlock']
)->name('whatsapp.blocks.destroy');
