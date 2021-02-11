<?php

namespace App\Http\Controllers\AcademicoDocente;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Input;

use App\User;

use App\AcademicoDocente\AsignacionProfesor;
use App\AcademicoDocente\CursoTieneDirectorGrupo;

use App\Core\Acl;


class ProfesorController extends Controller
{
	
	public function __construct()
    {
		$this->middleware('auth');
    }

    /**
     * Elimina un profesor.
     *
     */
    public function eliminar_profesor( $user_id )
    {
        $registro = User::find( $user_id );
        
        // Verificación 1: Tiene carga académica
        $cantidad = AsignacionProfesor::where('id_user', $user_id )->count();

        if($cantidad != 0){
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Profesor NO puede ser eliminado. Tiene carga académica asignada.');
        }
        
        // Verificación 2: Tiene curso asignado como director de grupo
        $cantidad = CursoTieneDirectorGrupo::where('user_id', $user_id )->count();

        if($cantidad != 0){
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Profesor NO puede ser eliminado. Está a asignado a un curso como director de grupo.');
        }
        
        // Verificación 3
        $cantidad = Acl::where('user_id', $user_id )->count();

        if($cantidad != 0){
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Profesor NO puede ser eliminado. Tiene restricciones de control de acceso (ACL) asignadas.');
        }
        
        // Verificación 4: Planes de clases

        $cantidad = Acl::where('user_id', $user_id )->count();

        if($cantidad != 0){
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Profesor NO puede ser eliminado. Tiene restricciones de control de acceso (ACL) asignadas.');
        }

        // Quitar permisos
        $registro->removeRole('Profesor');
        $registro->removeRole('Director de grupo');

        //Borrar Registro
        $registro->delete();

        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Profesor eliminado correctamente.');

    }
}