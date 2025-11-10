<?php

use App\Models\Form;
use App\Models\TypeForm;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/forms');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('forms', function () {
    $forms = Form::join('type_forms', 'forms.type_form_id', 'type_forms.id')->
    select('forms.*', 'type_forms.name as type', 'type_forms.str as str')->paginate(10);

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
    ]);

})->middleware(['auth', 'verified'])->name('forms');

require __DIR__.'/settings.php';
