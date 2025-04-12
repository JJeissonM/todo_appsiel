<?php

namespace App\Http\Controllers\AcademicoDocente;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;

use App\Http\Controllers\Matriculas\ObservadorEstudianteController;

use App\AcademicoDocente\AsignacionProfesor;

use App\Matriculas\Matricula;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Matriculas\PeriodoLectivo;

use App\Calificaciones\Asignatura;
use App\Calificaciones\Calificacion;
use App\Calificaciones\Periodo;
use App\Calificaciones\Logro;
use App\Calificaciones\NotaNivelacion;

use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Services\AsignaturasService;
use App\Matriculas\CatalogoAspecto;
use App\Matriculas\TiposAspecto;
use App\Matriculas\AspectosObservador;
use App\Matriculas\NovedadesObservador;
use App\Matriculas\FodaEstudiante;


use App\Core\Colegio;
use App\Matriculas\Services\ObservadorEstudianteService;
use App\Sistema\Modelo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class AcademicoDocenteController extends Controller
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
        $usuario = Auth::user();
        $colegio = Colegio::where('empresa_id', $usuario->empresa_id)->get()->first();

        if (!is_null($colegio)) {
            $periodo_lectivo = PeriodoLectivo::get_actual();
            $listado = AsignacionProfesor::get_asignaturas_x_curso($usuario->id, $periodo_lectivo->id);

            $miga_pan = [
                ['url' => 'NO', 'etiqueta' => 'Académico docente']
            ];

            $modelo_logros_id = Modelo::where('modelo', 'sga_logros')->get()->first()->id;

            $modelo_logros_adicionales_id = Modelo::where('modelo', 'sga_logros_adicionales')->get()->first()->id;

            $modelo_plan_clases_id = Modelo::where('modelo', 'PlanClaseEncabezado')->get()->first()->id;
            $modelo_guia_academica_id = Modelo::where('modelo', 'sga_guias_academicas')->get()->first()->id;

            return view('academico_docente.index', compact('listado', 'miga_pan', 'modelo_logros_id', 'periodo_lectivo', 'modelo_plan_clases_id', 'modelo_guia_academica_id', 'modelo_logros_adicionales_id'));
        } else {
            echo "La Empresa asociada al Usuario actual no tiene ningún Colegio asociado.";
        }
    }

    /**
     * Show the form for creating a LOGROS.
     *
     * @return \Illuminate\Http\Response
     */
    public function ingresar_logros($curso_id, $asignatura_id)
    {
        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

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

        $archivo_js = app($modelo->name_space)->archivo_js;

        $form_create = [
            'url' => json_decode(app($modelo->name_space)->urls_acciones)->store,
            'campos' => $lista_campos
        ];

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Ingresar logros']
        ];

        return view('layouts.create', compact('form_create', 'miga_pan', 'archivo_js'));
    }

    // Muestra listado de logros para revisar y editar
    public function revisar_logros($curso_id, $asignatura_id)
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

        $modelo = Modelo::find( Input::get('id_modelo') );
        
        $registros = app($modelo->name_space)->get_logros($colegio->id, $curso_id, $asignatura_id, null, $nro_registros, $search);
        $sqlString = app($modelo->name_space)->sqlString2($colegio->id, $curso_id, $asignatura_id, null, $search);
        $tituloExport = app($modelo->name_space)->tituloExport();

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => $modelo->descripcion]
        ];

        $id_app = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $encabezado_tabla = app($modelo->name_space)->encabezado_tabla;
        $titulo_tabla = '';
        $url_print = '';
        $url_ver = '';
        $url_estado = '';

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo');

        $urls_acciones = json_decode(app($modelo->name_space)->urls_acciones);

        $url_crear = '';
        $source = 'INDEX6';
        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $url_edit = 'academico_docente/modificar_logros/' . $curso_id . '/' . $asignatura_id . '/id_fila' . $variables_url;

        $url_eliminar = '';
        if ($modelo->id == 70) {
            $url_eliminar = 'academico_docente/eliminar_logros/' . $curso_id . '/' . $asignatura_id . '/id_fila' . $variables_url;
        }

        if ($modelo->id == 225) {
            $url_eliminar = 'web_eliminar/id_fila' . $variables_url;
        }

        return view('layouts.index', compact('registros', 'curso', 'asignatura', 'nro_registros', 'id_app', 'id_modelo', 'search', 'sqlString', 'source', 'tituloExport', 'miga_pan', 'url_crear', 'titulo_tabla', 'encabezado_tabla', 'url_edit', 'url_print', 'url_ver', 'url_estado', 'url_eliminar'));
    }


    // Muestra formulario para modificar logros
    public function modificar_logros($curso_id, $asignatura_id, $logro_id)
    {

        $registro = Logro::find($logro_id);

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo, '', 'edit');

        $url_update = json_decode(app($modelo->name_space)->urls_acciones)->update;

        $url_action = str_replace('id_fila', $logro_id, $url_update);

        $form_create = [
            'url' => $url_action,
            'campos' => $lista_campos
        ];


        // NO se usa el ModeloController para cambiar la $miga_pan
        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Modificar logros']
        ];

        return view('layouts.edit', compact('form_create', 'miga_pan', 'registro', 'url_action'));
    }


    // Elimina un logro
    public function eliminar_logros($curso_id, $asignatura_id, $logro_id)
    {
        $logro = Logro::find($logro_id);

        // Validación #1
        $periodo = Periodo::find($logro->periodo_id);
        if ($periodo->cerrado) {
            return redirect('academico_docente/revisar_logros/' . $curso_id . '/' . $asignatura_id . '?id=' . Input::get('id'))->with('mensaje_error', 'Logro no puede ser eliminado, El periodo está cerrado. Código Logro: ' . $logro->codigo);
        }

        // Validación #2
        $periodo_lectivo = PeriodoLectivo::find($periodo->periodo_lectivo_id);
        if ($periodo_lectivo->cerrado) {
            return redirect('academico_docente/revisar_logros/' . $curso_id . '/' . $asignatura_id . '?id=' . Input::get('id'))->with('mensaje_error', 'Logro no puede ser eliminado, El PERIODO LECTIVO está cerrado. Código Logro: ' . $logro->codigo);
        }

        $logro->delete();

        return redirect('academico_docente/revisar_logros/' . $curso_id . '/' . $asignatura_id . '?id=' . Input::get('id'))->with('flash_message', 'Logro Eliminado correctamente.');
    }

    //Selección de datos para calificar
    public function calificar1($curso_id, $asignatura_id)
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

        $cursos = Curso::opciones_campo_select();

        $periodos = Periodo::opciones_campo_select();

        $periodo_lectivo_id = null;
        
        $periodo_lectivo_actual = PeriodoLectivo::get_actual();
        if ($periodo_lectivo_actual != null) {
            $periodo_lectivo_id = $periodo_lectivo_actual->id;
        }

        $registros_asignaturas = (new AsignaturasService())->get_asignaturas_del_curso_por_usuario(  $curso_id, $periodo_lectivo_id, 'Activo' );

        $asignaturas[''] = '';
        foreach ($registros_asignaturas as $opcion)
        {
            $asignaturas[$opcion->asignatura->id] = $opcion->asignatura->descripcion;
        }

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Ingresar calificaciones']
        ];

        return view('academico_docente.create_calificacion', compact('periodos', 'cursos', 'miga_pan', 'periodo_lectivo_id', 'curso_id', 'asignatura_id', 'asignaturas'));
    }


    public function revisar_calificaciones($curso_id, $asignatura_id)
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

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

        $registros = Calificacion::get_calificaciones($colegio->id, $curso_id, $asignatura_id, null, $nro_registros, $search);

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Calificaciones']
        ];

        $source = "INDEX4";
        $id_app = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $url_ver = null;
        $url_estado = null;
        $url_print = null;
        $url_edit = null;
        $url_eliminar = null;
        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $sqlString = Calificacion::sqlString($colegio->id, $curso_id, $asignatura_id, null, $search);
        $tituloExport = Calificacion::tituloExport();

        $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Año', 'Periodo', 'Curso', 'Estudiante', 'Asignatura', 'Calificación'];

        return view('layouts.index', compact('registros', 'curso', 'asignatura', 'url_eliminar', 'url_edit', 'nro_registros', 'id_app', 'id_modelo', 'url_ver', 'url_estado', 'url_print', 'source', 'search', 'sqlString', 'tituloExport', 'encabezado_tabla', 'miga_pan'));
    }

    public function revisar_estudiantes($curso_id, $id_asignatura)
    {
        // Se obtienen los estudiantes con matriculas activas en el curso y año indicado
        $estudiantes = Matricula::estudiantes_matriculados($curso_id, PeriodoLectivo::get_actual()->id, 'Activo');

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($id_asignatura);

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Estudiantes']
        ];

        return view('academico_docente.revisar_estudiantes', compact('estudiantes', 'curso', 'asignatura', 'miga_pan'));
    }

    public function listar_estudiantes($curso_id, $id_asignatura)
    {
        // Se obtienen los estudiantes con matriculas activas en el curso y año indicado
        $estudiantes = Matricula::estudiantes_matriculados($curso_id, PeriodoLectivo::get_actual()->id, 'Activo');

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($id_asignatura);
        $usuario = Auth::user();
        $docente = $usuario->name;

        $colegio = Colegio::where('empresa_id', $usuario->empresa_id)->get()->first();
        $calificaciones = Calificacion::get_calificaciones_boletines( $colegio->id, $curso_id, $id_asignatura, null );

        $periodo_lectivo = PeriodoLectivo::get_actual();

        $tope_escala_valoracion_minima = EscalaValoracion::where( 'periodo_lectivo_id', $periodo_lectivo->id )->orderBy('calificacion_minima','ASC')->first()->calificacion_maxima; 
        
        $periodos = Periodo::get_activos_periodo_lectivo( $periodo_lectivo->id );
        // Excluir el periodo final
        foreach ($periodos as $key => $value)
        {
            if ( $value->periodo_de_promedios )
            {
                unset( $periodos[$key] );
            }
        }

        $tam_letra = '11';
        $view =  View::make( 'academico_docente.pdf_estudiantes1', compact('estudiantes', 'curso', 'asignatura', 'docente', 'periodos', 'calificaciones', 'tope_escala_valoracion_minima','colegio','tam_letra') )->render();
        $vista = View::make( 'layouts.pdf3', compact('view') )->render();
        $orientacion = 'landscape';

        //crear PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(($vista))->setPaper('Letter', $orientacion);
        return $pdf->stream('listado_estudiantes.pdf');
    }

    // FORMULARIOS PARA ACTUALIZAR  ASPECTOS
    public function valorar_aspectos_observador($id_estudiante)
    {
        $estudiante = Estudiante::get_datos_basicos($id_estudiante);
        $tipos_aspectos = TiposAspecto::all();
        $novedades = NovedadesObservador::where('id_estudiante', $id_estudiante)->get();
        $registros_analisis = FodaEstudiante::where('id_estudiante', $id_estudiante)->get();

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'academico_docente/revisar_estudiantes/curso_id/' . Input::get('curso_id') . '/id_asignatura/' . Input::get('asignatura_id') . '?id=' . Input::get('id'), 'etiqueta' => 'Estudiantes'],
            ['url' => 'NO', 'etiqueta' => 'Observador: Valoración de aspectos > ' . $estudiante->nombre_completo]
        ];

        
        $observador_serv = new ObservadorEstudianteService();
        $matricula_a_mostrar = $observador_serv->get_matricula_a_mostrar((int)Input::get('matricula_id'), $estudiante);

        $anio_matricula = $observador_serv->get_anio_matricula((int)Input::get('matricula_id'), $estudiante);

        $observacion_general = '';
        if ($matricula_a_mostrar != null) {
            $observacion_general = $matricula_a_mostrar->get_observacion_general();
        }

        $vec_matriculas[''] = '';

        return view('academico_docente.estudiantes.valorar_aspectos_observador', compact('tipos_aspectos', 'estudiante', 'novedades', 'registros_analisis', 'miga_pan', 'observacion_general', 'matricula_a_mostrar', 'anio_matricula', 'vec_matriculas'));
    }

    // PROCEDIMIENTO ALMACENAR ASPECTOS
    public function guardar_valoracion_aspectos_old(Request $request)
    {
        $estudiante = Estudiante::find($request->id_estudiante);

        $aspectos = CatalogoAspecto::all();
        for ($i = 0; $i < count($aspectos); $i++) {
            $aspecto_estudiante = AspectosObservador::where('id_aspecto', '=', $request->input('id_aspecto.' . $i))->where('id_estudiante', '=', $request->id_estudiante)->where('fecha_valoracion', 'like', date('Y') . '%')->count();
            if ($aspecto_estudiante == 0) {
                DB::insert(
                    'insert into sga_aspectos_observador 
                        (id_estudiante,id_aspecto,fecha_valoracion,valoracion_periodo1,valoracion_periodo2,valoracion_periodo3,valoracion_periodo4) values (?,?,?,?,?,?,?)',
                    [$request->id_estudiante, $request->input('id_aspecto.' . $i), $request->fecha_valoracion, $request->input('valoracion_periodo1.' . $i), $request->input('valoracion_periodo2.' . $i), $request->input('valoracion_periodo3.' . $i), $request->input('valoracion_periodo4.' . $i)]
                );
            } else {
                DB::table('sga_aspectos_observador')->where(['id' => $request->input('aspecto_estudiante_id.' . $i)])->update([
                    'valoracion_periodo1' => $request->input('valoracion_periodo1.' . $i), 'valoracion_periodo2' => $request->input('valoracion_periodo2.' . $i),
                    'valoracion_periodo3' => $request->input('valoracion_periodo3.' . $i), 'valoracion_periodo4' => $request->input('valoracion_periodo4.' . $i)
                ]);
            }
        }

        $matricula = Matricula::find($request->matricula_id);
        $matricula->observacion_general = $request->observacion_general;
        $matricula->save();

        return redirect('academico_docente/valorar_aspectos_observador/' . $estudiante->id . '?id=' . $request->url_id . '&curso_id=' . $request->curso_id . '&asignatura_id=' . $request->asignatura_id)->with('flash_message', 'Registros actualizados correctamente.');
    }
    
    public function guardar_valoracion_aspectos_fake()
    {
        return 'true';
    }
    
    //DELETE FROM `sga_aspectos_observador` WHERE `id_estudiante` = 26;

    public function guardar_valoracion_aspectos(Request $request)
    {
        $valoraciones_linea_aspecto = json_decode($request->valoraciones_linea_aspecto);

        foreach ($valoraciones_linea_aspecto as $key => $linea) {

            if ((int)$linea->aspecto_estudiante_id == 0) {
                $data = [
                        'id_estudiante' => $request->id_estudiante,
                        'id_aspecto' => $linea->id_aspecto,
                        'fecha_valoracion' => $request->fecha_valoracion,
                        'valoracion_periodo1' => $linea->valoracion_periodo1,
                        'valoracion_periodo2' => $linea->valoracion_periodo2,
                        'valoracion_periodo3' => $linea->valoracion_periodo3,
                        'valoracion_periodo4' => $linea->valoracion_periodo4
                    ];
                
                $aspecto = AspectosObservador::create($data);

            }else{
                $aspecto = AspectosObservador::find((int)$linea->aspecto_estudiante_id);
                $aspecto->fill([
                    'valoracion_periodo1' => $linea->valoracion_periodo1,
                    'valoracion_periodo2' => $linea->valoracion_periodo2,
                    'valoracion_periodo3' => $linea->valoracion_periodo3,
                    'valoracion_periodo4' => $linea->valoracion_periodo4
                ]);
                $aspecto->save();
            }
        }

        $matricula = Matricula::find($request->matricula_id);

        $matricula->observacion_general = $request->observacion_general;
        $matricula->save();

        return redirect('academico_docente/valorar_aspectos_observador/' . $request->id_estudiante . '?id=' . $request->url_id . '&curso_id=' . $request->curso_id . '&asignatura_id=' . $request->asignatura_id)->with('flash_message', 'Registros actualizados correctamente.');
    }
    
    public function redirect_valoracion_aspectos_guardados($estudiante_id, $url_id, $curso_id, $asignatura_id)
    {
        return redirect('academico_docente/valorar_aspectos_observador/' . $estudiante_id . '?id=' . $url_id . '&curso_id=' . $curso_id . '&asignatura_id=' . $asignatura_id)->with('flash_message', 'Registros actualizados correctamente.');
    }

    /**
     ** Vista previa del observador del estudiante.
     **/
    public function show_observador($id)
    {
        $view_pdf = (new ObservadorEstudianteService())->vista_preliminar($id, 'show');

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'academico_docente/revisar_estudiantes/curso_id/' . Input::get('curso_id') . '/id_asignatura/' . Input::get('asignatura_id') . '?id=' . Input::get('id'), 'etiqueta' => 'Estudiantes'],
            ['url' => 'NO', 'etiqueta' => 'Observador: Visualización']
        ];

        return view('academico_docente.estudiantes.observador_show', compact('miga_pan', 'view_pdf', 'id'));
    }

    public function ingresar_notas_nivelaciones($curso_id, $asignatura_id)
    {
        //$colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

        $periodos = Periodo::opciones_campo_select();

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $estudiantes = Matricula::estudiantes_matriculados($curso_id, PeriodoLectivo::get_actual()->id, 'Activo');

        $vec_estudiantes[''] = '';
        foreach ($estudiantes as $opcion) {
            $vec_estudiantes[$opcion->id] = $opcion->nombre_completo;
        }

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Ingresar notas de nivelaciones']
        ];

        return view('academico_docente.nivelaciones.formulario_precreate', compact('periodos', 'asignatura', 'curso', 'vec_estudiantes', 'miga_pan'));
    }

    public function notas_nivelaciones_cargar_estudiante(Request $request)
    {
        $estudiante = Estudiante::find($request->estudiante_id);
        $curso = Curso::find($request->curso_id);
        $periodo = Periodo::find($request->periodo_id);
        $asignatura = Asignatura::find($request->asignatura_id);
        $nota_nivelacion = (object)[
            'id' => 0,
            'estudiante' => $estudiante,
            'curso' => $curso,
            'periodo' => $periodo,
            'asignatura' => $asignatura,
            'calificacion' => '',
            'observacion' => ''
        ];

        $registro = NotaNivelacion::where([
            'periodo_id' => $request->periodo_id,
            'curso_id' => $request->curso_id,
            'asignatura_id' => $request->asignatura_id,
            'estudiante_id' => $request->estudiante_id
        ])->get()->first();

        if ($registro!=null) {
            $nota_nivelacion = $registro;
        }

        $mensaje = '';
        $clase_mensaje = 'success';
        $escala_valoracion_maxima = EscalaValoracion::get_min_max($periodo->periodo_lectivo_id)[1];

        $vista = View::make('academico_docente.nivelaciones.formulario_actualizar_nota', compact('nota_nivelacion', 'mensaje', 'escala_valoracion_maxima', 'clase_mensaje'))->render();

        return $vista;
    }

    public function notas_nivelaciones_actualizar(Request $request)
    {
        $estudiante = Estudiante::find($request->estudiante_id);
        $curso = Curso::find($request->curso_id);
        $periodo = Periodo::find($request->periodo_id);
        $asignatura = Asignatura::find($request->asignatura_id);
        $nota_nivelacion = (object)[
            'id' => 0,
            'estudiante' => $estudiante,
            'curso' => $curso,
            'periodo' => $periodo,
            'asignatura' => $asignatura,
            'calificacion' => '',
            'observacion' => ''
        ];

        $mensaje = 'Calificación en cero, no se afectó ninguna operación.';
        $clase_mensaje = 'default';

        $registro = NotaNivelacion::where([
            'periodo_id' => $request->periodo_id,
            'curso_id' => $request->curso_id,
            'asignatura_id' => $request->asignatura_id,
            'estudiante_id' => $request->estudiante_id
        ])->get()->first();

        if (is_null($registro)) {
            if ($request->calificacion != 0) {
                $nota_nivelacion = NotaNivelacion::create([
                    'periodo_id' => $request->periodo_id,
                    'curso_id' => $request->curso_id,
                    'asignatura_id' => $request->asignatura_id,
                    'estudiante_id' => $request->estudiante_id,
                    'calificacion' => $request->calificacion,
                    'observacion' => $request->observacion,
                    'creado_por' => Auth::user()->email
                ]);

                $mensaje = 'Nota de nivelación CREADA correctamente.';
                $clase_mensaje = 'success';
            }
        } else {

            if ($request->calificacion == 0) {
                $mensaje = 'Nota de nivelación ELIMINADA correctamente.';
                $clase_mensaje = 'warning';

                $registro->delete();
            } else {
                $registro->calificacion = $request->calificacion;
                $registro->observacion = $request->observacion;
                $registro->modificado_por = Auth::user()->email;
                $registro->save();
                $nota_nivelacion = $registro;
                $mensaje = 'Nota de nivelación MODIFICADA correctamente.';
                $clase_mensaje = 'info';
            }
        }

        $periodo = Periodo::find($request->periodo_id);
        $escala_valoracion_maxima = EscalaValoracion::get_min_max($periodo->periodo_lectivo_id)[1];

        $vista2 = View::make('academico_docente.nivelaciones.formulario_actualizar_nota', compact('nota_nivelacion', 'mensaje', 'escala_valoracion_maxima', 'clase_mensaje'))->render();

        return $vista2;
    }

    public function revisar_notas_nivelaciones($curso_id, $asignatura_id)
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

        $registros = NotaNivelacion::get_registros($curso_id, $asignatura_id, $nro_registros, $search);

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Notas de nivelaciones']
        ];
        $source = "INDEX5";
        $id_app = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $url_ver = null;
        $url_estado = null;
        $url_print = null;
        $url_edit = null;
        $url_eliminar = null;
        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $sqlString = NotaNivelacion::sqlString2($curso_id, $asignatura_id, $search);
        $tituloExport = NotaNivelacion::tituloExport();

        $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Año lectivo', 'Curso', 'Periodo', 'Asignatura', 'Calificación de nivelación', 'Observaciones'];

        return view('layouts.index', compact('registros', 'url_ver', 'url_edit', 'url_estado', 'url_print', 'url_eliminar', 'sqlString', 'asignatura', 'curso', 'nro_registros', 'source', 'tituloExport', 'search', 'id_app', 'id_modelo', 'encabezado_tabla', 'miga_pan'));
    }
}
