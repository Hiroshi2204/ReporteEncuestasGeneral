<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function create(Request $request){
        DB::beginTransaction();
        try {
            $persona = Persona::firstOrCreate(
                [
                    "numero_documento"=>$request->input('numero_documento'),
                ],
                [
                    "tipo_documento_id"=>1,
                    "nombres"=>$request->input('nombres'),
                    "apellido_paterno"=>$request->input('apellido_paterno'),
                    "apellido_materno"=>$request->input('apellido_materno'),
                    "celular"=>$request->input('celular'),
                    "correo"=>$request->input('correo'),
                    //"distrito_id"=>$$request->input('numero_documento'),
                ]
            );
            $usuario = User::firstOrCreate(
                [
                    "persona_id"=>$persona->id,
                    "username"=>$persona->numero_documento,
                ],
                [
                    "password"=>$request->password,
                ]
            );
        DB::commit();
        return redirect()->route('login')->with('success', 'Usuario creado exitosamente');
        //return response()->json(["resp" => "Usuario creado correctamente"], 200);
    }
        catch (Exception $e) {
            DB::rollback();
            return redirect()->route('login')->with('error', 'No se pudo crear');
            //return response()->json(["error" => "error " . $e], 500);
        }
    }
}
