<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\TypeForm;
use Exception;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            $data = $request->all();

            $record = $data['record'] ?? null;
            $uuidBase = $data['uuidBase'] ?? null;
            $token = $data['token'] ?? null;
            if ($token != 'goro4vmm.gd3') {
                return throw new Exception('Error token incorrect', 500);
            }

            // code...
            Form::create([
                'name' => $record['name_complete'],
                'form' => $record,
                'type_form_id' => TypeForm::whereStr($uuidBase)->first()->id,
            ]);
        } catch (\Exception $err) {
            return throw new Exception('Error Processing Request: . '.$err->getMessage(), 500);
        }

        return response()->json([
            'sucess' => true,
            'message' => 'guardado correctamente',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
