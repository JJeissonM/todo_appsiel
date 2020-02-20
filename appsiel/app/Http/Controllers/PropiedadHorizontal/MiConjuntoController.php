<?php

namespace App\Http\Controllers\PropiedadHorizontal;
use App\Http\Controllers\CxC\CxCController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

use DB;
use Auth;
use Storage;
use Input;
use Form;

use App\PropiedadHorizontal\PhAnuncio;
use App\PropiedadHorizontal\Propiedad;
use App\Core\Tercero;

class MiConjuntoController extends Controller
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
        $anuncios = PhAnuncio::where('core_empresa_id',Auth::user()->empresa_id)->where('estado','Activo')->get();

        $permisos = Permission::where('core_app_id',Input::get('id'))
                                ->where('parent',0)
                                ->orderBy('orden','ASC')
                                ->get()
                                ->toArray();

        return view('propiedad_horizontal.mi_conjunto.index', compact('anuncios', 'permisos') );
    }


    public function mi_cartera()
    {   
        
        $propiedad = Propiedad::where('email_arrendatario', Auth::user()->email )->get();

        if ( count($propiedad) > 0 ) 
        {
            $propiedad = $propiedad[0];

            $cartera = CxCController::get_cartera_inmueble( $propiedad->id, date('Y-m-d'), 'aqui_va_cualquier_valor' ); 
            $tercero = Tercero::find($propiedad->core_tercero_id);
        }else{
            $propiedad = new Propiedad;
            $tercero = new Tercero;
            $cartera = CxCController::get_cartera_inmueble( 0, date('Y-m-d'), 'aqui_va_cualquier_valor' );
        }  

        $miga_pan = [
                  ['url'=>'mi_conjunto?id='.Input::get('id'),'etiqueta'=>'Mi conjunto'],
                  ['url'=>'NO','etiqueta' => 'Estados de cuentas' ]
              ];


        return view('propiedad_horizontal.mi_conjunto.mi_cartera', compact('propiedad','tercero','cartera', 'miga_pan') );
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
