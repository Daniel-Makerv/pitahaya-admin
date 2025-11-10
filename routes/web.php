<?php

use App\Models\Form;
use App\Models\TypeForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/forms');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('forms', function (Request $request) {
    $search = $request->input('search');

    $formsQuery = Form::join('type_forms', 'forms.type_form_id', '=', 'type_forms.id')
        ->select('forms.*', 'type_forms.name as type', 'type_forms.str as str');

    // 👇 Filtro por búsqueda
    if ($search) {
        $formsQuery->where(function ($q) use ($search) {
            $q->where('forms.name', 'like', "%{$search}%")
                ->orWhere('forms.phone_contact', 'like', "%{$search}%")
                ->orWhere('type_forms.name', 'like', "%{$search}%"); // área de interés
        });
    }

    $forms = $formsQuery->paginate(10)->withQueryString(); // <- mantiene search en los links

    $counts = TypeForm::get()->map(function ($typeForm) {
        $total = Form::whereTypeFormId($typeForm->id)->count();

        return [
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

require __DIR__.'/settings.php';
