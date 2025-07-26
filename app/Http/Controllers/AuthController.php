<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;

class AuthController extends Controller
{
    public function login()
    {
        return view('mostrar_login');
    }
    public function authenticate(Request $request)
    {
        //return response()->json($request);
        /*$username = $request->input('username');
        $password = $request->input('password');*/
        $credentials = $request->only('username', 'password');
        $usernameu = User::where('username', $request->username)->first();
        if (!$usernameu) return redirect()->route('login');/*return response()->json(["error" => "El nombre de usuario no existe"], 400);*/
        $user = User::with('persona')->where('username', $request->username)->where('estado_registro', 'A')->first();

        if (!$user) return redirect()->route('login');/*return response()->json(['error' => 'Usuario bloqueado'], 402);*/

        try {
            $this->cambiarDuracionToken();
            if (!$token = FacadesJWTAuth::attempt($credentials)) {
                //return response()->json(['error' => 'invalid_credentials'], 403);
                return redirect()->route('login')->with('error', 'invalid_credentials');
            }
        } catch (JWTException $e) {
            //return response()->json(['error' => 'could_not_create_token'], 500);
            return redirect()->route('login')->with('error', 'could_not_create_token');
        }
        //session(['username' => $username]);
        $response = array(
            "id" => $user->id,
            "persona_id" => $user->persona_id,
            "username" => $user->username,
            "persona" => $user->persona,
        );
        $response['token'] = $token;
        return redirect()->intended("/menu/{$user->id}");
        //return response()->json($response);
    }



    public function getAuthenticatedUser()
    {
        try {
            if (!$user = FacadesJWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e);
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e);
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e);
        }
        return response()->json(compact('user'));
    }
    private function cambiarDuracionToken()
    {
        $myTTL = 60 * 24 * 1; // En minutos
        FacadesJWTAuth::factory()->setTTL($myTTL);
    }
    public function my()
    {
        $my = User::with('persona')->find(auth()->user()->id);
        
        $response = array(
            "id" => $my->id,
            "persona_id" => $my->persona_id,
            "username" => $my->username,
            "persona" => $my->persona,
        );

        return response()->json(["data" => $response]);
    }

    /**
     *
     */
    public function updatePassword(Request $request)
    {
        // Obtener usuario autenticado (logeado)
        $usuario = User::find(auth()->user()->id);
        // contraseña actual (insertar)
        $current_password = $request->current_password;
        //Nueva contraseña (insertar)
        $new_password = $request->new_password;
        // Confirmar la nueva contraseña
        $confirm_Password = $request->confirm_Password;

        if (Hash::check($current_password, $usuario->password)){
            if ($new_password == $confirm_Password) {
                $usuario->password = $new_password;
                $usuario->save();

                return response()->json(['resp' => 'La contraseña ha sido actualizada exitosamente'], 200);
            }else {
                return response()->json(['resp' => 'Las contraseñas no COINCIDEN, vuelva a insertar'], 400);
            }
        } else {
            return response()->json(['resp' => 'La contraseña actual no es correcta, inserte nuevamente.' ], 401);
        }
    }

}
