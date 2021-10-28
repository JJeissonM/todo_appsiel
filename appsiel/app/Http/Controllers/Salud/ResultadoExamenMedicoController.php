<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Input;
use DB;
use View;

use App\Sistema\Modelo;

use App\Salud\ExamenTieneVariables;
use App\Salud\ExamenTieneOrganos;
use App\Salud\ExamenMedico;
use App\Salud\ResultadoExamenMedico;
use App\Salud\Paciente;

class ResultadoExamenMedicoController extends ModeloController
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtienen los campos que tiene ese modelo
        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');

        $examen_id = Input::get('examen_id');

        $organos = ExamenTieneOrganos::leftJoin('salud_organos_del_cuerpo','salud_organos_del_cuerpo.id','=','salud_examen_tiene_organos.organo_id')
                                        ->where('examen_id', $examen_id)
                                        ->select('salud_organos_del_cuerpo.id','salud_organos_del_cuerpo.descripcion')
                                        ->orderBy('salud_examen_tiene_organos.orden')
                                        ->get();

        //Agregar campos de las variables asociadas al exámen
        $variables = ExamenTieneVariables::leftJoin('salud_catalogo_variables_examenes','salud_catalogo_variables_examenes.id','=','salud_examen_tiene_variables.variable_id')
                                        ->where('examen_id', $examen_id)
                                        ->select('salud_catalogo_variables_examenes.id','salud_catalogo_variables_examenes.descripcion','salud_examen_tiene_variables.tipo_campo')
                                        ->orderBy('salud_examen_tiene_variables.orden')
                                        ->get();

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        // Si tiene una accion diferente para el envío del formulario
        $url_action = 'web';
        if ($modelo->url_form_create != '')
        {
            $url_action = $modelo->url_form_create.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }

        $examen = ExamenMedico::find( $examen_id );

        return View::make( 'consultorio_medico.resultado_examen_create',compact('form_create','url_action','organos','variables','examen') )->render();
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

        $modelo = Modelo::find($request->url_id_modelo);

        $general->validar_requeridos_y_unicos($request, $modelo);

        // Armar los datos

        $datos = [ 
                    'paciente_id' => $request->paciente_id,
                    'consulta_id' => $request->consulta_id,
                    'examen_id' => $request->examen_id
                ];

        // Se guarda un registro por cada par: { variable: organo_del_cuerpo }
        foreach ( $request->all() as $key => $value ) 
        {
             /* 
                El del tag "name" de cada input del form create tiene un valor como la siguiente estructura:
                    campo_variable_organo-(variable_id)-(organo_del_cuerpo_id)
             */
            $campo = explode("-", $key);

            if ( $campo[0] == 'campo_variable_organo') 
            {
                $datos['variable_id'] = $campo[1];
                $datos['organo_del_cuerpo_id'] = $campo[2];
                $datos['valor_resultado'] = $value;

                app($modelo->name_space)->create( $datos );
            }
        }

        return response()->json( [ 'consulta_id'=>$request->consulta_id, 'examen_id' => $request->examen_id ] );
    }

    /**
     * Muestra la tabla del exámen médico en la ventana Modal
     *
     */
    public function show($id)
    {
        $datos = explode("-", $id); // IDs de consulta-paciente-examen

        $organos = ExamenTieneOrganos::leftJoin('salud_organos_del_cuerpo','salud_organos_del_cuerpo.id','=','salud_examen_tiene_organos.organo_id')->where( 'examen_id', $datos[2] )->select('salud_organos_del_cuerpo.id','salud_organos_del_cuerpo.descripcion')->orderBy('salud_examen_tiene_organos.orden')->get();

        $variables = ExamenTieneVariables::leftJoin('salud_catalogo_variables_examenes','salud_catalogo_variables_examenes.id','=','salud_examen_tiene_variables.variable_id')->where( 'examen_id', $datos[2] )->select('salud_catalogo_variables_examenes.id','salud_catalogo_variables_examenes.descripcion','salud_examen_tiene_variables.tipo_campo')->orderBy('salud_examen_tiene_variables.orden')->get();

        return View::make( 'consultorio_medico.resultado_examen_show_form', [ 'paciente_id' => $datos[1], 'consulta_id' => $datos[0], 'examen_id' => $datos[2], 'organos' => $organos, 'variables' => $variables ] );
    }

    public function get_tabla_resultado_examen( $consulta_id, $paciente_id, $examen_id)
    {
        $organos = ExamenTieneOrganos::leftJoin('salud_organos_del_cuerpo','salud_organos_del_cuerpo.id','=','salud_examen_tiene_organos.organo_id')->where( 'examen_id', $examen_id )->select('salud_organos_del_cuerpo.id','salud_organos_del_cuerpo.descripcion')->orderBy('salud_examen_tiene_organos.orden')->get();

        $variables = ExamenTieneVariables::leftJoin('salud_catalogo_variables_examenes','salud_catalogo_variables_examenes.id','=','salud_examen_tiene_variables.variable_id')->where( 'examen_id', $examen_id )->select('salud_catalogo_variables_examenes.id','salud_catalogo_variables_examenes.descripcion','salud_examen_tiene_variables.tipo_campo')->orderBy('salud_examen_tiene_variables.orden')->get();

        return View::make( 'consultorio_medico.resultado_examen_show_tabla', compact( 'paciente_id', 'consulta_id', 'examen_id', 'organos', 'variables') );
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
        foreach ( $request->all() as $key => $value ) 
        {
            $campo = explode("-", $key);
            if ( $campo[0] == 'campo_variable_organo') 
            {
                $matchThese = [ 'paciente_id' => $request->paciente_id, 'consulta_id' => $request->consulta_id, 'examen_id' =>  $request->examen_id, 'variable_id' => $campo[1], 'organo_del_cuerpo_id' => $campo[2] ];
                DB::table('salud_resultados_examenes')->updateOrInsert($matchThese,['valor_resultado' => $value]);
            }
        }

        return "Sí guardo.";
    }

    /**
     * Eliminar resultados ingresados de un exámen médico
     *
     */
    public function eliminar_resultado_examen_medico(Request $request)
    {
        // Verificación 1: Exámen está asociado a fórmula óptica
        $formula_id = DB::table('salud_formula_tiene_examenes')->where(['examen_id' => $request->examen_id2 ])->value('formula_id');

        if( !is_null($formula_id)  ){
          return redirect( $request->ruta_redirect )->with( 'mensaje_error', 'Registros de '.$request->lbl_descripcion_modelo_entidad.' NO pueden ser eliminados. El exámen Está asociado a la fórmula óptica.' );
        }

        $wheres = [ 'paciente_id' => $request->paciente_id2, 'consulta_id' => $request->consulta_id2, 'examen_id' =>  $request->examen_id2 ];

        ResultadoExamenMedico::where( $wheres )->delete();

        return redirect( $request->ruta_redirect )->with('mensaje_error', 'Registros de '.$request->lbl_descripcion_modelo_entidad.' ELIMINADOS correctamente.');
    }
}
