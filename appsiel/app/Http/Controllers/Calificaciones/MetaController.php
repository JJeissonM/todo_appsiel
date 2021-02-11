<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Requests;

use App\Calificaciones\Meta;
use App\Calificaciones\Asignatura;
use App\Calificaciones\Consecutivometa;

use App\Matriculas\Curso;

use App\Core\Colegio;
use App\Sistema\Modelo;

use PDF;
use Auth;
use Input;
use DB;

class MetaController extends Controller
{
    protected $modelo_id = 75;

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
        // Viene de ModeloController 
        return view('layouts.create', compact('form_create', 'miga_pan'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $registro_creado)
    {
        // Ya se creó el meta en ModeloController

        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];
        $id_colegio = $colegio->id;

        // Se obtiene el consecutivo para actualizar el meta creado
        $registro = DB::table('sys_secuencias_codigos')->where([
            ['id_colegio', $id_colegio],
            ['modulo', 'metas']
        ])->value('consecutivo');
        $consecutivo = $registro + 1;

        // Actualizar el consecutivo
        DB::table('sys_secuencias_codigos')->where([['id_colegio', $id_colegio], ['modulo', 'metas']])->increment('consecutivo');

        $registro_creado->codigo = $consecutivo;
        $registro_creado->save();


        if ($request->guardar_y_nuevo == "on") {
            $ruta = 'web/create';
        } else {
            $ruta = 'web';
        }

        return redirect($ruta . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'meta creado correctamente.');
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
        // obtener el registro
        $registro = Meta::find($id);

        /*
        if($meta->ocupado){
            return redirect('calificaciones/metas?id='.Input::get('id'))->with('mensaje_error','El registro está siendo utilizado por otro usuario. Por favor, intente más tarde.');
        }else{
            $meta->ocupado = true;
            $meta->save();
            return view('calificaciones.metas.edit',['meta'=>$meta]);
        }
        */

        $asignatura = Asignatura::find($registro->id_asignatura);
        $nom_asignatura = $asignatura->descripcion;

        // Escala de valoracion
        $opciones2 = DB::table('sga_escala_valoracion')->get();
        $vec2[''] = '';
        foreach ($opciones2 as $opcion) {
            $vec2[$opcion->id] = $opcion->nombre_escala . " (" . $opcion->calificacion_minima . "-" . $opcion->calificacion_maxima . ")";
        }
        $escala_valoracion = $vec2;

        $miga_pan = [
            ['url' => 'calificaciones/metas?id=' . Input::get('id'), 'etiqueta' => 'metas'],
            ['url' => 'NO', 'etiqueta' => 'Código: ' . $registro->codigo]
        ];

        return view('calificaciones.metas.edit', compact('registro', 'nom_asignatura', 'miga_pan', 'escala_valoracion'));
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

        switch ($id) {
            case 'buscar':
                $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];
                $id_colegio = $colegio->id;

                $opciones = "";
                $id_nivel = $request->id_nivel;
                $asignaturas = Asignatura::where(
                    [
                        ['nivel_grado', $id_nivel],
                        ['id_colegio', $id_colegio],
                        ['estado', "Activo"]
                    ]
                )->get();

                $opciones .= '<option value="">Seleccionar...</option>';
                foreach ($asignaturas as $campo) {
                    $opciones .= '<option value="' . $campo->id . '">' . $campo->descripcion . '</option>';
                }
                return $opciones;
                break;
            case 'editar':

                break;
                //return $id." ".$request->descripcion;
            case 'listado':
                //Preparar Vista

                $registros = DB::table('metas')
                    ->where('id_asignatura', $request->id_asignatura)
                    ->where('estado', 'Activo')
                    ->get();

                $nom_asignatura = Asignatura::where('id', '=', $request->id_asignatura)->value('descripcion');

                $orientacion = $request->orientacion;
                $tam_hoja = $request->tam_hoja;
                $cantidad_lineas = $request->cantidad_lineas;
                $view =  \View::make('calificaciones.metas.pdf_metas', compact('registros', 'nom_asignatura', 'tam_hoja', 'cantidad_lineas'))->render();

                //$oficio=array(216,326);
                //Renderizar PDF
                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);
                return $pdf->download('metas_' . $nom_asignatura . '.pdf'); //stream();
                break;
            default:
                // Cuando $id es cualquier valor, se asume que es el id del meta a editar

                return redirect('web/' . $id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'Registro MODIFICADO correctamente.');

                break;
        }
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


    public function proceso1()
    {
        //llenar_codigo_metas
        $cantidad = Meta::count();
        for ($i = 1; $i <= $cantidad; $i++) {
            DB::table('metas')
                ->where('id', $i)
                ->update(['codigo' => $i]);
            //echo "<br/>".$i; Esta salida no funciona, el controlador solo manda un nual final.
        }
        //echo "cant. metas: ".$cantidad;
    }

    public function consultar($asignatura)
    {
        $metas = DB::table('metas')->where('id_asignatura', $asignatura)->where('estado', 'Activo')->get();
        return view('calificaciones.metas.consultar', ['metas' => $metas, 'id_asignatura' => $asignatura]);
    }


    /**
     * Muestra formulario para listar metas
     */
    public function listar()
    {
        $opciones1 = DB::table('niveles')->get();

        $vec1[''] = '';
        foreach ($opciones1 as $opcion) {
            $vec1[$opcion->id] = $opcion->descripcion;
        }

        $niveles = $vec1;

        $miga_pan = [
            ['url' => 'calificaciones/metas?id=' . Input::get('id'), 'etiqueta' => 'metas'],
            ['url' => 'NO', 'etiqueta' => 'Listados']
        ];

        return view('calificaciones.metas.listar', compact('niveles', 'miga_pan'));
    }






    /**
     * Show the form for creating a LOGROS.
     *
     * @return \Illuminate\Http\Response
     */
    public function ingresar_metas($curso_id, $asignatura_id)
    {
        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find($this->modelo_id);

        $lista_campos = ModeloController::get_campos_modelo($modelo, '', 'create');

        // Se Personalizan los campos
        for ($i = 0; $i < count($lista_campos); $i++) {

            switch ($lista_campos[$i]['name']) {
                case 'curso_id':
                    $curso = Curso::find($curso_id);
                    $lista_campos[$i]['opciones'] = [$curso_id => $curso->descripcion];
                    break;
                case 'asignatura_id':
                    $asignatura = Asignatura::find($asignatura_id);
                    $lista_campos[$i]['opciones'] = [$asignatura_id => $asignatura->descripcion];
                    break;

                default:
                    # code...
                    break;
            }
        }

        // Se crear un array para generar el formulario
        // Este array se envía a la vista layouts.create, que carga la plantilla principal del formulario CREAR
        // La vista layouts.create incluye a la vista core.vistas.form_create que es la que usa al array form_create para generar un formulario html 
        $form_create = [
            'url' => $modelo->url_form_create,
            'campos' => $lista_campos
        ];

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Ingresar propósitos']
        ];

        //return view('academico_docente.ingresar_metas',compact('form_create','miga_pan'));
        return view('layouts.create', compact('form_create', 'miga_pan'));
    }


    // Almacenar nuevo logro creado 
    public function guardar_meta(Request $request, $logro_id = null)
    {
        // $logro_id se envía desde la ruta web/$logro_id/edit
        if (!is_null($logro_id))
        {
            $request['logro_id'] = $logro_id;
        }

        $modelo = Modelo::find($this->modelo_id);

        $datos = $request->all();

        if (isset($request->logro_id)) {
            // Se está moficando un logro
            $logro = Meta::find($request->logro_id);
            $logro->fill($datos);
            $logro->save();

            $lbl_mensaje = 'MODIFICADO';
        } else {
            // Almacenar el logro

            $registro_creado = app($modelo->name_space)->create($datos);

            $lbl_mensaje = 'CREADO';

            $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];
            $id_colegio = $colegio->id;

            // Se obtiene el consecutivo para actualizar el logro creado
            $registro = DB::table('sys_secuencias_codigos')->where([
                ['id_colegio', $id_colegio],
                ['modulo', 'logros']
            ])->value('consecutivo');
            $consecutivo = $registro + 1;

            // Actualizar el consecutivo
            DB::table('sys_secuencias_codigos')->where([['id_colegio', $id_colegio], ['modulo', 'logros']])->increment('consecutivo');

            $registro_creado->codigo = $consecutivo;
            $registro_creado->save();
        }

        if ($request->guardar_y_nuevo == "on") {
            $ruta = 'academico_docente/ingresar_metas/' . $request->curso_id . '/' . $request->asignatura_id;
        } else {
            $ruta = 'academico_docente';
        }

        return redirect($ruta . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'Propósito ' . $lbl_mensaje . ' correctamente.');
    }


    // Muestra listado de logros para revisar y editar
    public function revisar_metas($curso_id, $asignatura_id)
    {
        //determinar la cantidad de registros a mostrar
        $nro_registros = 10;
        $temp = Input::get('nro_registros');
        if ($temp != null) {
            $nro_registros = $temp;
        }
        //determinar la busqueda
        $search = "";
        $temp2 = Input::get('search');
        if ($temp2 != null) {
            $search = trim($temp2);
        }
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

        $registros = Meta::get_metas($colegio->id, $curso_id, $asignatura_id, $nro_registros, $search);
        $sqlString = Meta::sqlString2($colegio->id, $curso_id, $asignatura_id, $search);
        $tituloExport = Meta::tituloExport();

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Propósitos']
        ];

        $modelo = Modelo::find($this->modelo_id);

        $encabezado_tabla = app($modelo->name_space)->encabezado_tabla;

        $id_app = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $url_print = '';
        $url_ver = '';
        $url_custom = '';
        $url_estado = '';
        $url_eliminar = '';
        $source = 'INDEX7';
        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $titulo_tabla = '';

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo');

        $url_edit = 'academico_docente/modificar_metas/' . $curso_id . '/' . $asignatura_id . '/id_fila' . $variables_url;

        $url_eliminar = 'academico_docente/eliminar_metas/' . $curso_id . '/' . $asignatura_id . '/id_fila' . $variables_url;

        return view('layouts.index', compact('registros', 'nro_registros', 'source', 'id_app', 'id_modelo', 'curso', 'asignatura', 'search', 'sqlString', 'tituloExport', 'miga_pan', 'titulo_tabla', 'encabezado_tabla', 'url_edit', 'url_print', 'url_ver', 'url_estado', 'url_eliminar'));
    }


    // MUestra formulario para modificar logros
    public function modificar_metas($curso_id, $asignatura_id, $logro_id)
    {

        $registro = Meta::find($logro_id);

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find($this->modelo_id);

        $lista_campos = ModeloController::get_campos_modelo($modelo, '', 'create');

        // Se Personalizan los campos
        for ($i = 0; $i < count($lista_campos); $i++) {

            switch ($lista_campos[$i]['name']) {
                case 'curso_id':
                    $curso = Curso::find($curso_id);
                    $lista_campos[$i]['opciones'] = [$curso_id => $curso->descripcion];
                    break;
                case 'asignatura_id':
                    $asignatura = Asignatura::find($asignatura_id);
                    $lista_campos[$i]['opciones'] = [$asignatura_id => $asignatura->descripcion];
                    break;
                case 'escala_valoracion_id':
                    $lista_campos[$i]['value'] = $registro->escala_valoracion_id;
                    break;
                case 'descripcion':
                    $lista_campos[$i]['value'] = $registro->descripcion;
                    break;
                case 'periodo_id':
                    $lista_campos[$i]['value'] = $registro->periodo_id;
                    break;
                case 'estado':
                    $lista_campos[$i]['value'] = $registro->estado;
                    break;
                case 'guardar_y_nuevo_logro':
                    $lista_campos[$i]['value'] = '';
                    break;

                default:
                    # code...
                    break;
            }
        }

        // Crear un nuevo campo
        $lista_campos[$i]['tipo'] = 'hidden';
        $lista_campos[$i]['name'] = 'logro_id';
        $lista_campos[$i]['descripcion'] = '';
        $lista_campos[$i]['opciones'] = '';
        $lista_campos[$i]['value'] = $logro_id;
        $lista_campos[$i]['atributos'] = [];
        $lista_campos[$i]['requerido'] = true;

        $form_create = [
            'url' => $modelo->url_form_create,
            'campos' => $lista_campos
        ];

        $url_action = $modelo->url_form_create;

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Ingresar propósitos']
        ];

        //return view('academico_docente.ingresar_metas',compact('form_create','miga_pan'));
        return view('layouts.create', compact('form_create', 'miga_pan', 'registro', 'url_action'));
    }


    // Elimina un logro
    public function eliminar_metas($curso_id, $asignatura_id, $logro_id)
    {

        $logro = Meta::find($logro_id);

        $logro->delete();

        return redirect('academico_docente/revisar_metas/' . $curso_id . '/' . $asignatura_id . '?id=' . Input::get('id'))->with('flash_message', 'Propósito Eliminado correctamente.');
    }
}
