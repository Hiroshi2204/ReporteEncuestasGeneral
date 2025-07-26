<?php

namespace App\Http\Controllers;

use App\Models\AlmacenProducto;
use App\Models\Destinatario;
use App\Models\EgresosAdicionales;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\ProductoDetalle;
use App\Models\Proveedor;
use App\Models\RegistroEntrada;
use App\Models\RegistroEntradaDetalle;
use App\Models\RegistroSalida;
use App\Models\RegistroSalidaDetalle;
use App\Models\Trabajador;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
//use Auth;
use Illuminate\Support\Facades\Auth as Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Tymon\JWTAuth\JWTAuth;

class LoginController extends Controller
{
    public function mostrar_login()
    {
        return view('mostrar_login');
    }
    // public static function cambiarDuracionToken()
    // {
    //     JWTAuth::factory()->setTTL(60 * 24 * 30);
    // }
    public function mostrar_menu($username)
    {
        $usernameu = User::where('id', $username)->first();

        if (!$usernameu) {
            return redirect()->route('login');
            //return response()->json(["error" => "El nombre de usuario no existe"], 400);
        }

        $user = User::with('persona')->where('id', $username)->where('estado_registro', 'A')->first();

        if (!$user) {
            return redirect()->route('login');
            //return response()->json(['error' => 'Usuario bloqueado'], 402);
        }
        $response = [
            "nombres" => $user->persona->nombres,
            "apellido_paterno" => $user->persona->apellido_paterno,
            "apellido_materno" => $user->persona->apellido_materno,
            "username" => $user->username,
            "celular" => $user->persona->celular,
            "correo" => $user->persona->correo,
            "id" => $user->id
        ];

        //$response['token'] = $token;
        //return response()->json($response);
        return view('menu_principal', $response);
    }
    public function mostrar_usuario()
    {
        return view('crear_login');
    }
    //------------------------------------------------------------------------
    public function asignar_rol()
    {
        return view('asignar_rol_trabajador');
    }
    public function crear_asignar_rol(Request $request)
    {
        $persona = Persona::where('numero_documento', $request->input('numero_documento'))->first();
        //return response()->json($persona);
        if ($persona) {
            $existe_personal_empresa = Trabajador::where('persona_id', $persona->id)->where('estado_registro', 'A')->first();
            if ($existe_personal_empresa) {
                return redirect()->back()->with('error', 'Ya existe otro registro con el numero de documento.');
                //return response()->json(["error" => "Ya existe otro registro con el numero de documento"], 500);
            }
            $usuario = User::updateOrCreate([
                'persona_id' => $persona->id,
                'username' => $persona->numero_documento,
            ], [
                'password' => $persona->numero_documento,
                'estado_registro' => 'A'
            ]);
            $personal = Trabajador::updateOrCreate([
                'persona_id' => $persona->id,
            ], [
                'rol_id' =>  $request->input('rol_id'),
                'empresa_id' => "1",
                'estado_registro' => 'A'
            ]);
            return redirect()->back()->with('success', 'Datos guardados exitosamente.');
            //return response()->json(["resp" => "Personal creado correctamente"], 200);
        } else {
            return redirect()->back()->with('error', 'No existe registro con el numero de documento.');
        }
    }
    public function cambiar_rol($id)
    {
        $user = User::with('persona')->where('id', $id)->where('estado_registro', 'A')->first();
        return view('cambiar_rol_trabajador', compact('user'));
    }
    public function cambiar_rol_trabajador(Request $request, $id)
    {
        $request->validate([
            'rol_id' => 'required|numeric',
        ]);
        // Obtener el trabajador
        $trabajador = Trabajador::findOrFail($id);

        // Actualizar el rol
        $trabajador->update(['rol_id' => $request->input('rol_id')]);

        // Redirigir de vuelta con un mensaje de éxito
        //return View::make('cambiar_rol_trabajador', compact('trabajador', 'roles'));
        return redirect()->back()->with('success', 'Rol cambiado exitosamente.');
    }
    public function buscar_trabajador(Request $request)
    {
        $persona = Persona::where('numero_documento', $request->input('numero_documento'))->first();

        if ($persona) {
            $datos = [
                'numero_documento' => $persona->numero_documento,
                "nombres" => $persona->nombres,
                "apellido_paterno" => $persona->apellido_paterno,
                "apellido_materno" => $persona->apellido_materno,
                "celular" => $persona->celular,
                "correo" => $persona->correo,
            ];

            return view('buscar_trabajador', ['datos' => $datos]);
        } else {
            // Manejar el caso en que no se encuentra el trabajador
            return view('buscar_trabajador', ['datos' => null]);
        }
    }
    //------------------------------------------------------------------------
    public function buscar_producto()
    {
        $productos = Producto::with('color')
            ->where('estado_registro', 'A')
            ->get();

        $datos = [];

        foreach ($productos as $producto) {
            $nom_producto = $producto->nom_producto ?? null;
            $cod_producto = $producto->cod_producto ?? null;
            $largo = $producto->largo ?? null;
            $color = $producto->color->nombre ?? null;
            $estado_registro = $producto->estado_registro ?? null;

            $datos[] = [
                "nom_producto" => $nom_producto ?? null,
                "cod_producto" => $cod_producto ?? null,
                "largo" => $largo ?? null,
                "color" => $color ?? null,
                "estado_registro" => $estado_registro ?? null,
            ];
        }
        return view('buscar_producto', ['datos' => $datos]);
    }
    public function act_producto()
    {
        //$user = User::with('persona')->where('id', auth()->user()->id)->first();
        $productos = Producto::all();
        return view('actualizar_producto', ['productos' => $productos]);
        //return view('actualizar_producto');
    }
    public function actualizar_producto(Request $request)
    {
        DB::beginTransaction();

        try {

            $producto = Producto::find($request->input('id'));
            //$detalle = ProductoDetalle::where('producto_id', $request->input('id'))->where('estado_registro', 'A')->first();

            $producto->update([
                "descripcion" => $request->input('descripcion'),
                "cantidad" => $request->input('cantidad'),
                "marca_id" => $request->input('marca_id'),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Producto actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["error" => "Error al actualizar el producto: " . $e->getMessage()], 500);
        }
    }
    public function producto_eliminado()
    {
        return view('eliminar_producto');
    }
    public function eliminar_producto(Request $request)
    {
        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($request->input('nom_producto'));
            //$detalle = ProductoDetalle::where('producto_id', $request->input('nom_producto'))->first();

            $producto->update([
                "estado_registro" => "I",
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["error" => "Error al eliminar el producto: " . $e->getMessage()], 500);
        }
    }
    public function almacen()
    {
        return view('almacen');
    }
    public function registrar_producto()
    {
        // if (auth()->check()) {
        //     $datos = User::with('persona')->where('id', auth()->user()->id)->first();
        //     return view('registrar_producto', compact('datos'));
        // } else {
        //     return redirect()->back()->with('error', 'No se puede.'); 
        // }
        // $datos = User::with('persona')->where('id', auth()->user()->id)->first();
        // return view('registrar_producto',$datos);
        return view('registrar_producto');
    }
    public function crear_varios_producto(Request $request)
    {
        $productos = $request->input('productos');

        if (!is_array($productos)) {
            return response()->json(["resp" => "La lista de productos no es válida"], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($productos as $productoData) {
                // Verificar si $productoData es un array antes de intentar acceder a sus elementos
                if (!is_array($productoData)) {
                    // Manejar el caso donde $productoData no es un array
                    DB::rollback();
                    return response()->json(["resp" => "Error al crear productos: Formato de datos incorrecto"], 400);
                }

                $id_producto = Producto::where('nom_producto',  $productoData['nom_producto'])
                    ->where('cod_producto',  $productoData['cod_producto'])
                    // ->where('color',  $productoData['color'])
                    // ->where('origen',  $productoData['origen'])
                    ->first();
                if ($id_producto) {
                    $peso_producto = Producto::where('cod_producto',  $productoData['cod_producto'])
                        // ->where('cod_producto',  $productoData['cod_producto'])
                        // ->where('color',  $productoData['color'])
                        // ->where('origen',  $productoData['origen'])
                        ->first();
                    //return response()->json($cantidad_producto);
                    $largo_total =  $productoData['largo'] + $peso_producto->largo;
                    $producto = Producto::updateOrCreate([
                        "nom_producto" =>  $productoData['nom_producto'],
                        "cod_producto" =>  $productoData['cod_producto'],
                        "color_id" =>  $productoData['color_id'],
                        "origen" => $productoData['origen'],
                    ], [
                        "peso_neto" => $largo_total,
                        //"marca_id" =>  $productoData['marca_id']
                    ]);
                    // $entrada = RegistroEntrada::create([
                    //     "fecha_entrada" => Carbon::now(),
                    //     "proveedor" => $productoData['proveedor'],
                    //     "almacen_id" => 1
                    // ]);
                    // $entrada_detalle = RegistroEntradaDetalle::create([
                    //     "producto_id" => $id_producto->id,
                    //     "precio" => $productoData['precio'],
                    //     "cantidad" => $productoData['cantidad'],
                    //     "registro_entrada_id" => $entrada->id
                    // ]);
                    DB::commit();
                    //return response()->json(["resp" => "Producto creado correctamente"], 200);
                } else {
                    $producto = Producto::create([
                        "nom_producto" => $productoData['nom_producto'],
                        "cod_producto" => $productoData['cod_producto'],
                        "color_id" => $productoData['color_id'],
                        //"origen" => $productoData['origen'],
                        "largo" => $productoData['largo'],
                        "espesor" => $productoData['espesor'],
                        "lote" => $productoData['lote'],
                        //"marca_id" => $productoData['marca_id'],
                    ]);
                    // $entrada = RegistroEntrada::create([
                    //     "fecha_entrada" => Carbon::now(),
                    //     "proveedor" => $productoData['proveedor'],
                    //     "almacen_id" => 1
                    // ]);
                    // $entrada_detalle = RegistroEntradaDetalle::create([
                    //     "producto_id" => $producto->id,
                    //     "precio" => $productoData['precio'],
                    //     "cantidad" => $productoData['cantidad'],
                    //     "registro_entrada_id" => $entrada->id
                    // ]);
                    DB::commit();
                    //return response()->json(["resp" => "Producto creado correctamente"], 200);
                }
            }

            DB::commit();

            // Redirigir a la vista después de crear los productos
            return redirect()->route('registrar_producto')->with('success', 'Productos creados correctamente');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["resp" => "Error al crear productos: " . $e->getMessage()], 500);
        }
    }
    public function asignar_producto()
    {
        return view('asignar_producto');
    }
    public function asignar_varios_producto(Request $request)
    {
        $productos = $request->input('productos');

        if (!is_array($productos)) {
            return response()->json(["resp" => "La lista de productos no es válida"], 400);
        }

        DB::beginTransaction();
        try {
            $entrada = RegistroEntrada::create([
                "fecha_entrada" => Carbon::now(),
                // "proveedor_id" => $request->input('proveedor_id'),
                "almacen_id" => 1
            ]);

            foreach ($productos as $productoData) {
                if (!is_array($productoData)) {
                    DB::rollback();
                    return response()->json(["resp" => "Formato de datos incorrecto"], 400);
                }

                $producto = Producto::where('id', $productoData['id'])->first();

                if ($producto === null) {
                    DB::rollback();
                    return response()->json(["resp" => "Producto no encontrado: " . $productoData['id']], 404);
                }

                $entrada_detalle = RegistroEntradaDetalle::create([
                    "producto_id" => $producto->id,
                    "precio" => $productoData['precio'],
                    "largo" => $producto->largo,
                    "registro_entrada_id" => $entrada->id
                ]);
            }

            DB::commit();
            return redirect()->route('asignar_producto')->with('success', 'Productos asignados correctamente');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["resp" => "Error al asignar productos: " . $e->getMessage()], 500);
        }
    }
    public function exportar_productos()
    {
        return view('exportar_producto');
    }
    public function exportar_varios_productos(Request $request)
    {
        $productos = $request->input('productos');

        if (!is_array($productos)) {
            return response()->json(["resp" => "La lista de productos no es válida"], 400);
        }
        DB::beginTransaction();
        try {
            $salida = RegistroSalida::create([
                "fecha_salida" => Carbon::now(),
                //"destinatario_id" => $request->input('destinatario_id'),
                "almacen_id" => 1
            ]);
            foreach ($productos as $productoData) {
                if (!is_array($productoData)) {
                    DB::rollback();
                    //return response()->json(["resp" => "Error al exportar productos: Formato de datos incorrecto"], 400);
                }
                $id_producto = Producto::where('id', $productoData['id'])->first();
                if ($productoData['largo'] > $id_producto->largo)
                    return response()->json(["resp" => "No hay suficientes productos"]);
                $largo_total = $id_producto->largo - $productoData['largo'];
                $producto = Producto::updateOrCreate([
                    "id" => $productoData['id'],
                ], [
                    "largo" => $largo_total,
                ]);
                $entrada_detalle = RegistroSalidaDetalle::create([
                    "producto_id" => $id_producto->id,
                    //"peso_neto" => $productoData['peso_neto'],
                    "precio" => $productoData['precio'],
                    "largo" => $productoData['largo'],
                    "registro_salida_id" => $salida->id
                ]);
            }
            DB::commit();
            return redirect()->route('exportar_producto')->with('success', 'Productos exportados correctamente');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["resp" => "Error al exportar productos: " . $e->getMessage()], 500);
        }
    }
    //------------------------------------------------------------------------
    public function create_proveedor_mostrar()
    {
        return view('proveedor_crear');
    }
    public function create_proveedor(Request $request)
    {
        DB::beginTransaction();
        try {
            $proveedor = Proveedor::create([
                "proveedor" => $request->input('proveedor')
            ]);
            DB::commit();
            return redirect()->route('proveedor_crear')->with('success', 'Proveedor creado correctamente');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["error" => "error " . $e], 500);
        }
    }
    public function update_proveedor_mostrar()
    {
        return view('proveedor_actualizar');
    }
    public function update_proveedor(Request $request)
    {
        DB::beginTransaction();
        try {
            $proveedor = Proveedor::find($request->input('id'));
            $proveedor->update([
                "proveedor" => $request->input('proveedor')
            ]);
            DB::commit();
            return redirect()->route('proveedor_actualizar')->with('success', 'Proveedor actualizado correctamente');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["error" => "error " . $e], 500);
        }
    }
    public function delete_proveedor_mostrar()
    {
        return view('proveedor_eliminar');
    }
    public function delete_proveedor(Request $request)
    {
        DB::beginTransaction();

        try {
            $proveedor = Proveedor::find($request->input('id'));
            //$detalle = ProductoDetalle::where('producto_id', $request->input('nom_producto'))->first();

            $proveedor->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Proveedor eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["error" => "Error al eliminar el producto: " . $e->getMessage()], 500);
        }
    }
    public function get_proveedor()
    {
        try {
            $proveedores = Proveedor::get();

            $datos = [];

            foreach ($proveedores as $proveedor) {
                $nombre = $proveedor->proveedor ?? null;

                $datos[] = [
                    "proveedor" => $nombre ?? null,
                ];
            }
            return view('proveedor_ver', ['datos' => $datos]);
        } catch (Exception $e) {
            return response()->json(["resp" => "error", "error" => "Error al llamar a los proveedores  " . $e], 400);
        }
    }
    //------------------------------------------------------------------------
    public function create_destinatario_mostrar()
    {
        return view('destinatario_crear');
    }
    public function create_destinatario(Request $request)
    {
        DB::beginTransaction();
        try {
            $destinatario = Destinatario::create([
                "destinatario" => $request->input('destinatario')
            ]);
            DB::commit();
            return redirect()->route('destinatario_crear')->with('success', 'Destinatario creado correctamente');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["error" => "error " . $e], 500);
        }
    }
    public function update_destinatario_mostrar()
    {
        return view('destinatario_actualizar');
    }
    public function update_destinatario(Request $request)
    {
        DB::beginTransaction();
        try {
            $destinatario = Destinatario::find($request->input('id'));
            $destinatario->update([
                "destinatario" => $request->input('destinatario')
            ]);
            DB::commit();
            return redirect()->route('destinatario_actualizar')->with('success', 'Destinatario actualizado correctamente');
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["error" => "error " . $e], 500);
        }
    }
    public function delete_destinatario_mostrar()
    {
        return view('destinatario_eliminar');
    }
    public function delete_destinatario(Request $request)
    {
        DB::beginTransaction();

        try {
            $destinatario = Destinatario::find($request->input('id'));
            //$detalle = ProductoDetalle::where('producto_id', $request->input('nom_producto'))->first();

            $destinatario->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Proveedor eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["error" => "Error al eliminar el producto: " . $e->getMessage()], 500);
        }
    }
    public function get_destinatario()
    {
        try {
            $destinatarios = Destinatario::get();

            $datos = [];

            foreach ($destinatarios as $destinatario) {
                $nombre = $destinatario->destinatario ?? null;

                $datos[] = [
                    "destinatario" => $nombre ?? null,
                ];
            }
            return view('destinatario_ver', ['datos' => $datos]);
        } catch (Exception $e) {
            return response()->json(["resp" => "error", "error" => "Error al llamar a los destinatarios  " . $e], 400);
        }
    }
    //-----------------------------------------------------
    public function registrar_egreso()
    {
        return view('registrar_egreso');
    }
    public function crear_varios_egresos(Request $request)
    {
        $productos = $request->input('productos');

        if (!is_array($productos)) {
            return response()->json(["resp" => "La lista de productos no es válida"], 400);
        }
        DB::beginTransaction();
        try {
            foreach ($productos as $productoData) {
                if (!is_array($productoData)) {
                    DB::rollback();
                    return response()->json(["resp" => "Error al crear egresos: Formato de datos incorrecto"], 400);
                }
                    $producto = EgresosAdicionales::create([
                        "empresa_id" => 1,
                        "descripcion" => $productoData['descripcion'],
                        "costo" => $productoData['costo'],
                        "fecha_egreso" => Carbon::now()
                    ]);
                    DB::commit();
            }
            DB::commit();
            return redirect()->route('registrar_egreso')->with('success', 'Egresos creados correctamente');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["resp" => "Error al crear egresos: " . $e->getMessage()], 500);
        }
    }
}
