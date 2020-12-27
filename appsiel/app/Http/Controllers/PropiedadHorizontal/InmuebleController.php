<?php

namespace App\Http\Controllers\PropiedadHorizontal;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Input;
use DB;
use Auth;
use Hash;

use App\Core\Tercero;

use App\User;

use App\Contabilidad\ContabMovimiento;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class InmuebleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * CREAR UN INMUEBLE
     *
     * @return \Illuminate\Http\Response
     */
    public function create($form_create, $miga_pan)
    {
        // Viene de ModeloController
        return view('layouts.create',compact('form_create','miga_pan'));
    }

    /**
     * YA SE GUARDÓ EL INMUEBLE, AHORA SE CREA EL USUARIO ASOCIADO AL TERCERO QUE SE ASOCIÓ AL INMUEBLE
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $registro_creado)
    {
        // Viene de ModeloController
        
        // Se procede a crear el usuario asociado al inmueble

        $name = $request->nombre_arrendatario;

        $email = $request->email_arrendatario;

        $pass = ModeloController::generar_cadena_aleatoria(6);

        $user = User::create([
                        'empresa_id'=>Auth::user()->empresa_id, 
                        'name'=>$name,
                        'email'=>$email, 
                        'password'=>Hash::make($pass) ]);



        $role_id = 8; // 8 = Residentes
        
        $role_r = Role::where('id', '=', $role_id)->firstOrFail();            
        $user->assignRole($role_r); //Assigning role to user

        // Se actualiza el email la tabla core_terceros
        Tercero::where('id',$request->core_tercero_id)->update(['email'=>$email]);
        
        // Enviar a ModeloController@show para visualizar el registro creado
        return redirect( 'web/'.$registro_creado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with('flash_message','Inmueble creado correctamente. También se creó el usuario para acceso al sistema:<br/>
            <spam style="background-color: yellow;">Usuario: '.$email.'<br/> Contraseña: '.$pass.'</spam>');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Ya se actualizaron los datos de la propiedad en ModeloController

        // Se valida si cambia el email, para actualizar el usuario. La tabla core_terceros aún tiene el email ANTERIOR
        $tercero = Tercero::find($request->core_tercero_id);
        if ( $tercero->email != $request->email_arrendatario)
        {
            // Si el email fue cambiado, se toma el email ANTERIOR para validar si el usuario existe y luego ACTUALIZAR al usuario
            $email = $tercero->email;
        }else{
            // Si el email es el mismo, solo se verifica si el inmueble ya tiene un usuario creado
            $email = $request->email_arrendatario;
        }

        $usuario = User::where('email',$email)->get();

        if ( count($usuario) > 0 ) {
            $usuario_creado = false;


            if ( $tercero->email != $request->email_arrendatario)
            {
                // Si el email fue cambiado, se actuliza el email del user
                $usuario[0]->email = $request->email_arrendatario;
                $usuario[0]->save();
            }
            

            $mensaje = 'Inmueble MODIFICADO correctamente.';
        }else{


            // Si aún no tiene usuario creado, se le crea
            $usuario_creado = true;

            $name = $tercero->descripcion;

            $pass = ModeloController::generar_cadena_aleatoria(6);

            $user = User::create([
                            'empresa_id'=>Auth::user()->empresa_id, 
                            'name'=>$name,
                            'email'=>$email, 
                            'password'=>Hash::make($pass) ]);

            $role_id = 8; // 8 = Residentes
            
            $role_r = Role::where('id', '=', $role_id)->firstOrFail();            
            $user->assignRole($role_r); //Assigning role to user

            $mensaje = 'Inmueble MODIFICADO correctamente. También se creó el usuario para acceso al sistema:<br/>
            <spam style="background-color: yellow;">Usuario: '.$email.'<br/> Contraseña: '.$pass.'</spam>';
        }

        // Se actualiza el email del tercero
        $tercero->email = $request->email_arrendatario;
        $tercero->save(); 

        
        // Enviar a ModeloController@show para visualizar el registro modificado
        return redirect('web/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message',$mensaje);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}