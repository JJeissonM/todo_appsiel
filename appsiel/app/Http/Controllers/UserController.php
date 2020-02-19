<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Auth;
use DB;
use Hash;
use Input;

// Modelos
use App\User;
use App\Sistema\Modelo;
use App\Sistema\Aplicacion;
use App\Sistema\Campo;
use App\Core\Empresa;

use App\Matriculas\Estudiante;

use App\Http\Controllers\Sistema\ModeloController;


//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

class UserController extends ModeloController
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate name, email and password fields
        $this->validate( $request, [
                                        'name'=>'required|max:120',
                                        'email'=>'required|email|unique:users',
                                        'password'=>'required|min:6|confirmed',
                                        'role'=>'required'
                                    ]
                        );

        $user = User::create([
            					'empresa_id'=>Auth::user()->empresa_id, 
            					'name'=>$request->name,
            					'email'=>$request->email, 
            					'password'=>Hash::make($request->password)
                            ]);
		
		$role_r = Role::where('id', '=', $request->role)->firstOrFail();            
		$user->assignRole($role_r); //Assigning role to user
		
        return redirect( 'web?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with('flash_message','Usuario creado correctamente.');
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
        $user = User::findOrFail($id); //Get role specified by id

		//Validate name, email and password fields  
        $this->validate($request, [
                                    'name'=>'required|max:120',
                                    'email'=>'required|email|unique:users,email,'.$id,
                                    'role'=>'required'
                                ]);
		
        $user->fill( $request->all() )
                ->save();

        // Se borran los roles actuales y se le asigna el enviado en el request
        $user->roles()->sync( [ $request->role ] );
		
        return redirect( 'web?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with('flash_message','Usuario MODIFICADO correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Find a user with a given id and delete
        $user = User::findOrFail($id);

        $user->roles()->sync( [ ] ); // borrar todos los roles y asignar los del array (en este caso vacío)
        $user->delete();

        return redirect()->route('core.usuarios.index')
            ->with('flash_message',
             'User successfully deleted.');
    }


    // Tareas del administrador sobre el usuario

    // Formulario para cambiar contraseña
    public function form_cambiarpasswd( $user_id )
    {

        $registro = User::find( $user_id );

        // web?id=1&id_modelo=29
        if ( !is_null( Input::get('id_user') ) )
        {
            $registro = User::find( Input::get('id_user') );
        }

        if ( !is_null(Input::get('estudiante_id') ) )
        {
            $estudiante = Estudiante::find( Input::get('estudiante_id') );
            $registro = User::find( $estudiante->user_id );
        }

        if ( is_null($registro) )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Usuario no ha sido creado.');
        }

        if (!isset($miga_pan)) {
            $miga_pan = [
                            [ 'url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta' => $this->aplicacion->descripcion ],
                            [ 'url' => 'NO','etiqueta' => 'Cambiar contraseña: '.$registro->name.' ('.$registro->email.')' ]
                        ];
        }
        

        return view('core.usuario.cambiarpasswd',compact('registro','miga_pan'));
    }

    /**
     * Guardar cambio de contraseña del usuario
     *
     */
    public function cambiarpasswd(Request $request)
    {
        $this->validate($request,[
            'password_new'=>'required',
            'password_retype'=>'required'
		]);
            
        $usuario = User::findOrFail($request->user_id);

        if ( isset($request->url_id_modelo) )
        {
            $modelo_id = $request->url_id_modelo;
            $app_id = $request->url_id;
        }else{
            $modelo_id = 5;
            $app_id = 7;
        }
		
        // Si las contraseñas no coinciden
        if ($request->password_new != $request->password_retype) 
        {
            return redirect( '/core/usuario/cambiarpasswd/'.$request->user_id.'?id='.$app_id.'&id_modelo='.$modelo_id )->with('mensaje_error','La nuevas contraseñas no coinciden, por favor intente nuevamente.');
        }

        $usuario->password = Hash::make($request->password_new);
        $usuario->save();

        return redirect( 'web?id='.$app_id.'&id_modelo='.$modelo_id )->with('flash_message','Se cambió correctamente la contraseña de '.$usuario->name);
    }

    // TAREAS PARA EL PROPIO USUARIO
    public function perfil()
    {
        $usuario = Auth::user();
        $empresa = Empresa::find($usuario->empresa_id);

        $app = Aplicacion::find(Input::get('id'));

        if ( !isset($app->id) ) 
        {
            $app = (object)['app' => 'inicio', 'descripcion' => 'Inicio'];
        }

        $miga_pan = [
                        ['url'=>$app->app.'?id='.Input::get('id'),'etiqueta'=>$app->descripcion],
                        ['url'=>'NO','etiqueta'=>'Perfil de usuario']
                    ];

        return view('core.usuario.perfil.mi_perfil',compact('usuario','empresa','miga_pan'));
    }

    public function form_cambiar_empresa()
    {
        $empresa_id = Auth::user()->empresa_id;
        $registros = Empresa::all();
        $empresas="";
        foreach ($registros as $empresa) {
            if ($empresa->id == $empresa_id) {
                $empresas .= '<option value="'.$empresa->id.'" selected="selected">'.$empresa->descripcion.'</option>';
            }else{
                $empresas .= '<option value="'.$empresa->id.'">'.$empresa->descripcion.'</option>';
            }
            
        }

        $url_store = 'core/usuario/perfil/cambiar_empresa';

        return [$empresas,$url_store];
    }

    public function cambiar_empresa(Request $request)
    {
        $this->validate($request,[
            'empresa_id'=>'required'
        ]);
        
        $usuario = User::find($request->user_id);
        $usuario->empresa_id = $request->empresa_id;
        $usuario->save();

        return redirect('core/usuario/perfil?id='.$request->id)->with('flash_message','Se cambió correctamente la empresa.');
    }

    public function form_cambiar_mi_passwd()
    {
        $usuario = Auth::user();

        $miga_pan = [
                        ['url'=>'inicio','etiqueta'=>'Inicio'],
                        ['url'=>'core/usuario/perfil?id='.Input::get('id'),'etiqueta'=>'Perfil de usuario'],
                        ['url'=>'NO','etiqueta' => 'Cambiar contraseña']
                    ];

        return view('core.usuario.perfil.cambiar_mi_passwd',compact('usuario','miga_pan'));
    }


    public function cambiar_mi_passwd(Request $request)
    {

        $this->validate($request,[
            'password'=>'required',
            'password_new'=>'required',
            'password_retype'=>'required'
        ]);
        
        $usuario = User::find($request->user_id);
        
        //Se verifica la contraseña actual
        if (Hash::check($request->password, $usuario->password)) {
            
            //Se verifca que coincidan las nuevas contraseñas
            if ($request->password_new!=$request->password_retype) {
                return redirect('/core/usuario/perfil/cambiar_mi_passwd?id=7&id_modelo=5&ruta='.$request->ruta.'?id=7&id_modelo=5')->with('mensaje_error','La nuevas contraseñas no coinciden, por favor intente nuevamente.');
            }
            
            $usuario->password = Hash::make($request->password_new);
            $usuario->save();
            return redirect('/core/usuario/perfil?id=7&id_modelo=5')->with('flash_message','Se cambió correctamente la contraseña.');
        }else{
            return redirect('/core/usuario/perfil/cambiar_mi_passwd?id=7&id_modelo=5&ruta='.$request->ruta.'?id=7&id_modelo=5')->with('mensaje_error','La contraseña actual no es correcta, por favor intente nuevamente.');
        }
        
        
    }

    public function dar_permisos_usuario( $user_id, $permisos)
    {
        $user = User::find( $user_id );

        // Los permisos deben venir en un string separados por comas
        $vec = explode(",", $permisos);
        $cant = count($vec);
        for ($i=0; $i < $cant; $i++) 
        { 
            $user->givePermissionTo( $vec[$i] );
        }      

        echo "permisos concedidos";
    }
}
