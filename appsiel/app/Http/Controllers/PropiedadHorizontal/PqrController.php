<?php

namespace App\Http\Controllers\PropiedadHorizontal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Sistema\ModeloController;

use DB;
use Auth;
use Storage;
use Input;

use App\PropiedadHorizontal\PhAnuncio;
use App\PropiedadHorizontal\PhNotasPqr;
use App\PropiedadHorizontal\PhPqr;

use App\Sistema\TipoTransaccion;

class PqrController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($form_create, $miga_pan)
    {

        // ESTO ES SOLO PARA QUE NO SE GENERE UN ERROR EN EL GENEREALCONTROLLER
        // Este Modelo no maneja tipo de transacción
        $id_transaccion = 1;
        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $cantidad_campos = count($form_create['campos']);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$form_create['campos'],$cantidad_campos,'create');

        $form_create = [
                        'url' => '$modelo->url_form_create',
                        'campos' => $lista_campos
                    ];

        return view('layouts.create',compact('form_create','miga_pan','hola'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$registro)
    {
        
        $registro->fecha = date('Y-m-d');
        $registro->save();

        return redirect('web/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro CREADO correctamente.');
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
        return redirect('web/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro CREADO correctamente.');
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

    public function guardar_nota(Request $request){
    	// Se obtiene el modelo "Padre"
        //$modelo = Modelo::find($request->url_id_modelo);

        //$datos = app($modelo->name_space)->get_datos_asignacion();

        $this->validate($request, ['detalle' => 'required']);
        
        $registro = new PhNotasPqr;
        $registro->ph_pqr_id = $request->registro_modelo_padre_id;
        $registro->detalle = $request->detalle;
        $registro->fecha = date('Y-m-d H:i:s');
        $registro->estado = 'Publicada';
        $registro->creado_por = Auth::user()->email;
        $registro->modificado_por = Auth::user()->email;
        $registro->save();

        return redirect('web/'.$request->registro_modelo_padre_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Nota CREADA correctamente'); 
    }
}
