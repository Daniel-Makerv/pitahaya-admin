<?php

use App\Models\Form;
use App\Models\TypeForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormsExport;
use App\Exports\FormsMultiSheetExport;


Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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


    if($typeId == null || $typeId == ""){
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

require __DIR__.'/settings.php';
