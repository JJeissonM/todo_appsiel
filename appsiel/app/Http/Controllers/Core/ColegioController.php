<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Storage;
use Session;
use DB;
use Auth;
use Input;

use App\Core\Colegio;
use App\Core\Empresa;

class ColegioController extends Controller
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
    public function create($form_create,$miga_pan)
    {
       return view('layouts.create',compact('form_create','miga_pan'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$registro)
    {
        // YA SE GUADO EL COLEGIO!!!


        // Inicializar consecutivos para: INCRIPCIONES, MATRICULAS Y LOGROS 
        
        /*
            ESTOS CÓDIGOS SE PUEDEN INICIALIZAR AL MOMENTO DE USARLOS O INCREMENTAR SU CVALOR, IGUAL QUE LOS CONSECUTIVOS DE DOCUMENTOS
        */
        
        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id,'inscripciones', 0, date('Y'),'anio-consecutivo','Activo', date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);

        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id,'matriculas',0, date('Y'),'anio-consecutivo-grado','Activo',date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);

        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id,'logros',0, date('Y'),'consecutivo','Activo',date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);

        return redirect( 'web/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo );
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
		$registro = Colegio::find($id);
		$return = $_GET['return'];
		$ruta_imgescudo = '/storage/app/escudos/escudo_'.$registro->id.'.jpg';
		

        $miga_pan = [
                        ['url'=>'configuracion?id='.Input::get('id'),'etiqueta'=>'Configuración'],
                        ['url'=>'core/colegios?id='.Input::get('id'),'etiqueta'=>'Colegios'],
                        ['url'=>'NO','etiqueta'=>$registro->descripcion]
                    ];

        $datos = ['titulo' => 'Modificando el registro',
                    'accion' => 'edit_con_files',
                    'url' => 'core/colegios/'.$registro->id,
                    'metodo' => 'PUT',
                    'registro' => $registro,
                    'ruta_campos_form' => 'core.colegios.edit'];

        return view('core.vistas.formulario',compact('registro','ruta_imgescudo','return','miga_pan','datos'));
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
		return redirect('web?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');
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
