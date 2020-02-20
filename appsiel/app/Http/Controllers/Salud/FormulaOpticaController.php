<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Input;
use DB;
use Auth;
use View;

use App\Sistema\Modelo;
use App\Core\Empresa;
use App\Core\FirmaAutorizada;

use App\Salud\ExamenTieneVariables;
use App\Salud\ExamenTieneOrganos;
use App\Salud\ExamenMedico;
use App\Salud\ConsultaMedica;
use App\Salud\Paciente;
use App\Salud\ProfesionalSalud;
use App\Salud\FormulaOptica;

class FormulaOpticaController extends ModeloController
{
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $general = new ModeloController();

        // Se obtiene el modelo
        $modelo = Modelo::where('modelo','salud_formulas_opticas')->first();

        // Se obtienen los campos que tiene ese modelo
        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        // Si tiene una accion diferente para el envío del formulario
        $url_action = 'web';
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }

        $miga_pan = $this->get_miga_pan($modelo,'Crear nuevo');

        $examen = ExamenMedico::find( Input::get('examen_id') );

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($modelo->name_space)->archivo_js;

        return view('layouts.create',compact('form_create','miga_pan','archivo_js','url_action'));
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
        $general->crear_nuevo_registro( $request );

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
        return $general->edit( $id );
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
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        // LLamar a los campos del modelo para verificar los que son requeridos
        // y los que son únicos
        $lista_campos = $modelo->campos->toArray();
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) {
            if ( $lista_campos[$i]['editable'] == 1 ) 
            { 
                if ($lista_campos[$i]['requerido']) 
                {
                    $this->validate($request,[$lista_campos[$i]['name']=>'required']);
                }
                if ($lista_campos[$i]['unico']) 
                {
                    $this->validate($request,[$lista_campos[$i]['name']=>'unique:'.$registro->getTable().','.$lista_campos[$i]['name'].','.$id]);
                }
            }
            // Cuando se edita una transacción
            if ($lista_campos[$i]['name']=='movimiento') {
                $lista_campos[$i]['value']=1;
            }
        }

        // Se verifican si vienen campos con valores tipo array. Normalmente para los campos tipo chexkbox.
        foreach ( $request->all() as $key => $value)
        {
            if ( is_array($value) )
            {
                $request[$key] = implode(",", $value);
            }
        }

        //dd( $request->all() );

        $registro->fill( $request->all() );
        $registro->save();

        return redirect('consultorio_medico/pacientes/'.$request->paciente_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Fórmula MODIFICADA correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function eliminar_formula_optica(Request $request)
    {
      // 1ro. Eliminar la asociación de exámenes a la formula
      DB::table('salud_formula_tiene_examenes')->where( ['formula_id' => $request->formula_id] )->delete();

      FormulaOptica::find($request->formula_id)->delete();

      return redirect( $request->ruta_redirect )->with('mensaje_error', 'Fórmula óptica ELIMINADA correctamente.');
    }

    public function quitar_examen_de_formula($formula_id, $examen_id)
    {
        DB::table('salud_formula_tiene_examenes')->where( [ 'formula_id' => $formula_id, 'examen_id' => $examen_id] )->delete();

        return 1;
    }

    public function asociar_examen_a_formula($formula_id, $examen_id)
    {
        DB::table('salud_formula_tiene_examenes')->insert( [ 'formula_id' => $formula_id, 'examen_id' => $examen_id] );
        return 1;
    }

    public function imprimir($id)
    {
        $paciente_id = Input::get('paciente_id');
        $consulta_id = Input::get('consulta_id');
        $formula_id = $id;

        $consulta = ConsultaMedica::find($consulta_id);

        $datos_historia_clinica = Paciente::datos_basicos_historia_clinica( $paciente_id );

        $formula = FormulaOptica::find( $formula_id );

        // EXÁMENES
        $examenes = '';
        $opciones = ExamenMedico::where('estado','Activo')->orderBy('orden')->get();
        $i = 0;
        foreach ($opciones as $opcion){
            $esta = DB::table('salud_formula_tiene_examenes')->where( ['formula_id' => $formula_id, 'examen_id'=>$opcion->id] )->first();
            if ( !empty($esta) )
            {
               $examen_id = $opcion->id;

              $organos = ExamenTieneOrganos::leftJoin('salud_organos_del_cuerpo','salud_organos_del_cuerpo.id','=','salud_examen_tiene_organos.organo_id')->where( 'examen_id', $examen_id )->select('salud_organos_del_cuerpo.id','salud_organos_del_cuerpo.descripcion')->orderBy('salud_examen_tiene_organos.orden')->get();

              $variables = ExamenTieneVariables::leftJoin('salud_catalogo_variables_examenes','salud_catalogo_variables_examenes.id','=','salud_examen_tiene_variables.variable_id')->where( 'examen_id', $examen_id )->select('salud_catalogo_variables_examenes.id','salud_catalogo_variables_examenes.descripcion','salud_examen_tiene_variables.tipo_campo')->orderBy('salud_examen_tiene_variables.orden')->get();

              $examenes .= '<b>'.$opcion->descripcion.'</b>'.View::make('consultorio_medico.resultado_examen_show_tabla', compact('variables','organos','paciente_id','consulta_id','examen_id'))->render();// .'<br>'
            }
        }
        
        // PROFESIONAL DE LA SALUD
        $raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS nombre_completo';

        $profesional_salud = ProfesionalSalud::leftJoin('core_terceros','core_terceros.id','=','salud_profesionales.core_tercero_id')->select(DB::raw($raw),'salud_profesionales.especialidad','salud_profesionales.numero_carnet_licencia','salud_profesionales.core_tercero_id')->first();

        $firma_autorizada = FirmaAutorizada::get_firma_tercero( $profesional_salud->core_tercero_id );

        $empresa = Empresa::find( Auth::user()->empresa_id );

        // Se prepara el PDF
        $tam_hoja = 'Letter';//array(0, 0, 612.00, 792.00);//
        $orientacion='portrait';

        $view =  View::make('consultorio_medico.formula_optica_print_pdf', compact('consulta','datos_historia_clinica','examenes','anamnesis','resultados','profesional_salud','empresa','formula','firma_autorizada'))->render();

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);

        //return $view;
        return $pdf->download('formula_optica.pdf');//stream();
    }
}   