<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\TypeForm;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $token = $request->bearerToken();

        // ✅ Leer el UUID y los datos del cuerpo JSON
        $uuid = $request->input('uuid');
        $data = $request->input('data');

            if ($token != 'goro4vmm.gd3') {
                Log::debug('error token');

                return throw new Exception('Error token incorrect', 500);
            }

            // code...
            Form::create([
                'name' => $data['name_complete'],
                'form' => $data,
                'type_form_id' => TypeForm::whereStr($uuid)->first()->id,
            ]);
        } catch (\Exception $err) {
            Log::debug('error: '.$err->getMessage());

            return throw new Exception('Error Processing Request: . '.$err->getMessage(), 500);
        }
        Log::debug('regiistro guardado con exito');

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
