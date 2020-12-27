<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use Input;
use DB;
use PDF;
use Auth;
use Storage;
use View;
use File;
use Hash;

use App\Http\Requests;

use Spatie\Permission\Models\Role;

use App\Core\PasswordReset;

use App\UserHasRole;
use App\User;

use App\Sistema\Permiso;

class ProcesoController extends ModeloController
{

    public function principal( $vista_proceso )
    {
        if( !view()->exists($vista_proceso) )
        {
            return redirect( url()->previous() )->with('mensaje_error','Vista NO existe: ' . $vista_proceso );
        }

        $permiso = Permiso::where( 'url', 'index_procesos/'.$vista_proceso )->get()->first();

        if ( is_null( $permiso ) )
        {
            return redirect( url()->previous() )->with('flash_message','Proceso no existe');
        }

        $miga_pan = [
                        ['url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta'=> $this->aplicacion->descripcion ],
                        ['url' => 'NO','etiqueta'=> 'Proceso: ' . $permiso->descripcion ]
                    ];

        return view( $vista_proceso, compact( 'miga_pan' ) );
    }

}
