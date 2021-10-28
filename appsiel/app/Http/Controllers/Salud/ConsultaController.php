<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\ModeloEavController;

use Auth;
use DB;
use Input;
use Storage;
use View;

use App\Sistema\Html\MigaPan;

use App\Sistema\Aplicacion;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Tercero;
use App\Core\Empresa;
use App\Core\ModeloEavValor;

use App\Salud\ConsultaMedica;
use App\Salud\ExamenMedico;
use App\Salud\Paciente;
use App\Salud\ProfesionalSalud;
use App\Salud\ResultadoExamenMedico;
use App\Salud\ExamenTieneOrganos;
use App\Salud\ExamenTieneVariables;
use App\Salud\FormulaOptica;

class ConsultaController extends Controller
{
    protected $aplicacion, $modelo;

    public function __construct()
    {
        $this->middleware('auth');
        $this->modelo = Modelo::find( Input::get('id_modelo') );
        $this->aplicacion = Aplicacion::find( Input::get('id') );
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
    public function create()
    {
        $general = new ModeloController();

        return $general->create();
    }


    public function create2()
    {
        $general = new ModeloController();

        // Se obtienen los campos que el Modelo tiene asignados
        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');

        $acciones = $general->acciones_basicas_modelo($this->modelo, '');

        $form_create = [
                            'url' => $acciones->store,
                            'campos' => $lista_campos
                        ];

        $miga_pan = MigaPan::get_array($this->aplicacion, $this->modelo, 'Crear nueva. Paciente: ' . Paciente::find( Input::get('paciente_id') )->tercero->descripcion );

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $vista = 'consultorio_medico.consultas.crud';

        return view( $vista, compact('form_create', 'miga_pan', 'archivo_js') );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $general = new ModeloController();

        // Almacenar datos del Paciente
        $registro_creado = $general->crear_nuevo_registro( $request );

        $modelo_pacientes = Modelo::where('modelo','salud_pacientes')->first();

        return redirect( 'consultorio_medico/pacientes/'.$request->paciente_id.'?id='.$request->url_id.'&id_modelo='.$modelo_pacientes->id )->with( 'flash_message','Registro CREADO correctamente.' );
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
        $general = new ModeloController();

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find( Input::get('id_modelo') );

        //dd( Input::get('id_modelo'), $modelo);

        // Se obtiene el registro a modificar del modelo
        $registro = app( $modelo->name_space )->find($id);

        $lista_campos = $general->get_campos_modelo($modelo,$registro,'edit');

        $form_create = [
                        'url' => $modelo->url_form_create . '/' . $id,
                        'campos' => $lista_campos
                    ];
        
        /*$paciente = Paciente::datos_basicos_historia_clinica( Input::get('paciente_id') );

        $miga_pan = [
                        ['url'=>'consultorio_medico?id='.Input::get('id'),'etiqueta'=>'Consultorio Médico'],
                        ['url'=>'consultorio_medico/pacientes/'.Input::get('paciente_id').'?id='.Input::get('id').'&id_modelo=95','etiqueta'=>'Historia Clínica ' . $paciente->nombres." ".$paciente->apellidos ],
                        ['url'=>'NO', 'etiqueta' => "Modificar consulta"]
                    ];

        */

        $datos_columnas = true;

        return View::make( 'layouts.modelo_form_edit_sin_botones', compact('form_create','registro','datos_columnas') )->render(); 
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
        $consulta = ConsultaMedica::find($id);

        $consulta->fill( $request->all() );
        $consulta->save();

        return View::make( 'consultorio_medico.consultas.datos_consulta', compact('consulta') )->render();
    }

    /**
     * Validar y eliminar una consulta
     */
    public function delete($id)
    {
      $modelo = Modelo::find( Input::get('id_modelo') );
      $registro = ConsultaMedica::find( $id );

      // Verificación 1: Está en tabla EAV
      $modelo_entidad_id = ModeloEavValor::where(['modelo_padre_id'=>$modelo->id, 'registro_modelo_padre_id'=> $id])->value('modelo_entidad_id');
      $modelo_entidad = Modelo::find( $modelo_entidad_id );

      if( !is_null($modelo_entidad_id)  ){
          return redirect( 'consultorio_medico/pacientes/'.Input::get('paciente_id').'?id='.Input::get('id').'&id_modelo='.Input::get('modelo_pacientes_id') )->with( 'mensaje_error','Consulta NO puede ser eliminada. Tiene información en '.$modelo_entidad->descripcion );
      }
        
      // Verificación 2: Está en resultados de exámenes
      $examen_id = ResultadoExamenMedico::where(['consulta_id'=>$id])->value('examen_id');

      if( !is_null($examen_id)  ){
          return redirect( 'consultorio_medico/pacientes/'.Input::get('paciente_id').'?id='.Input::get('id').'&id_modelo='.Input::get('modelo_pacientes_id') )->with( 'mensaje_error','Consulta NO puede ser eliminada. Tiene información de Exámenes.' );
      }
        
      // Verificación 3: Está en fórmula óptica
      $paciente_id = FormulaOptica::where( ['consulta_id' => $id] )->value('paciente_id');

      if( !is_null($paciente_id)  ){
          return redirect( 'consultorio_medico/pacientes/'.Input::get('paciente_id').'?id='.Input::get('id').'&id_modelo='.Input::get('modelo_pacientes_id') )->with( 'mensaje_error','Consulta NO puede ser eliminada. Tiene información de Fórmula Óptica.' );
      }

      //Borrar Registro
      $registro->delete();

      return redirect( 'consultorio_medico/pacientes/'.Input::get('paciente_id').'?id='.Input::get('id').'&id_modelo='.Input::get('modelo_pacientes_id') )->with( 'flash_message','Consulta eliminada correctamente.' );
      
    }

    public function imprimir($id)
    {
        $paciente_id = Input::get('paciente_id');
        $consulta_id = $id;

        $consulta = ConsultaMedica::find($consulta_id);

        $datos_historia_clinica = Paciente::datos_basicos_historia_clinica( $paciente_id );

        // EXÁMENES
        $examenes = '';
        $opciones = ExamenMedico::where('estado','Activo')->get();
        $i = 0;
        foreach ($opciones as $opcion)
        {
            $esta = DB::table('salud_resultados_examenes')->where( ['examen_id'=>$opcion->id, 'paciente_id'=>$paciente_id,'consulta_id'=>$consulta_id] )->first();
            if ( !empty($esta) )
            {
              $examen_id = $opcion->id;

              $organos = ExamenTieneOrganos::leftJoin('salud_organos_del_cuerpo','salud_organos_del_cuerpo.id','=','salud_examen_tiene_organos.organo_id')->where( 'examen_id', $examen_id )->select('salud_organos_del_cuerpo.id','salud_organos_del_cuerpo.descripcion')->orderBy('salud_examen_tiene_organos.orden')->get();

              $variables = ExamenTieneVariables::leftJoin('salud_catalogo_variables_examenes','salud_catalogo_variables_examenes.id','=','salud_examen_tiene_variables.variable_id')->where( 'examen_id', $examen_id )->select('salud_catalogo_variables_examenes.id','salud_catalogo_variables_examenes.descripcion','salud_examen_tiene_variables.tipo_campo')->orderBy('salud_examen_tiene_variables.orden')->get();

              $examenes .= '<h4>'.$opcion->descripcion.'</h4>'.View::make('consultorio_medico.resultado_examen_show_tabla', compact('variables','organos','paciente_id','consulta_id','examen_id'))->render();
            }
        }
        
        // Anamnesis
        $modelo_padre_id = 96; // Consultas Médicas
        $registro_modelo_padre_id = $consulta->id;
        $modelo_entidad_id = 110; // Anamnesis
        $anamnesis = ModeloEavController::show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_id );

        // Resultados
        $modelo_padre_id = 96; // Consultas Médicas
        $registro_modelo_padre_id = $consulta->id;
        $modelo_entidad_id = 111; // Resultados de la consulta
        $resultados = ModeloEavController::show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_id );

        // PROFESIONAL DE LA SALUD
        $raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS nombre_completo';
        $profesional_salud = ProfesionalSalud::leftJoin('core_terceros','core_terceros.id','=','salud_profesionales.core_tercero_id')->select(DB::raw($raw),'salud_profesionales.especialidad','salud_profesionales.numero_carnet_licencia')->first();
        
        $empresa = Empresa::find( Auth::user()->empresa_id );
        // Se prepara el PDF
        $tam_hoja = 'Letter';
        $orientacion='portrait';

        $view =  View::make('consultorio_medico.consultas_print_pdf', compact('consulta','datos_historia_clinica','examenes','anamnesis','resultados','profesional_salud','empresa'))->render();

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);

        //return $view;
        return $pdf->download('historia_clinica.pdf');//stream();
    }
}
