<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;

use Input;
use DB;
use Auth;
use View;

use App\Sistema\Modelo;
use App\Core\Empresa;
use App\Core\FirmaAutorizada;
use App\Core\Tercero;

use App\Salud\ExamenTieneVariables;
use App\Salud\ExamenTieneOrganos;
use App\Salud\ExamenMedico;
use App\Salud\ConsultaMedica;
use App\Salud\Paciente;
use App\Salud\ProfesionalSalud;
use App\Salud\FormulaOptica;
use App\Salud\TipoLente;

class SaludOcupacionalController extends ModeloController
{
    
    public function imprimir_historia_medica_ocupacional( $id )
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

        $view =  View::make('consultorio_medico_salud_ocupacional.formato_1_historia_clinica', compact('consulta','datos_historia_clinica','examenes','anamnesis','resultados','profesional_salud','empresa'))->render();

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);

        //return $view;
        return $pdf->stream( 'historia_clinica.pdf');//stream();
    }


    public function generar_documento_vista( $formula_id, $paciente_id, $consulta_id )
    {
        $consulta = ConsultaMedica::find($consulta_id);

        $datos_historia_clinica = Paciente::datos_basicos_historia_clinica( $paciente_id );

        // EXÁMENES
        $examenes = '';
        //$opciones = ExamenMedico::where('estado','Activo')->orderBy('orden')->get();
        $opciones = ExamenMedico::examenes_del_paciente2( $paciente_id, $consulta_id );
        $i = 0;
        foreach ($opciones as $opcion)
        {
            $formula = FormulaOptica::leftJoin('salud_formula_tiene_examenes','salud_formula_tiene_examenes.formula_id','=','salud_formulas_opticas.id')
                        ->where('salud_formulas_opticas.paciente_id', $paciente_id)
                        ->where('salud_formulas_opticas.consulta_id', $consulta_id)
                        ->where('salud_formula_tiene_examenes.examen_id', $opcion->id)
                        ->get()
                        ->first();
            
            $formula_id = 0;
            $vista_formula = '';
            if( !is_null( $formula ) )
            {
              $vista_formula = View::make( 'consultorio_medico.formula_optica_show_tabla', compact('formula') )->render();
              $formula_id = $formula->id;

              $examen_id = $opcion->id;

              $organos = ExamenTieneOrganos::leftJoin('salud_organos_del_cuerpo','salud_organos_del_cuerpo.id','=','salud_examen_tiene_organos.organo_id')->where( 'examen_id', $examen_id )->select('salud_organos_del_cuerpo.id','salud_organos_del_cuerpo.descripcion')->orderBy('salud_examen_tiene_organos.orden')->get();

              $variables = ExamenTieneVariables::leftJoin('salud_catalogo_variables_examenes','salud_catalogo_variables_examenes.id','=','salud_examen_tiene_variables.variable_id')->where( 'examen_id', $examen_id )->select('salud_catalogo_variables_examenes.id','salud_catalogo_variables_examenes.descripcion','salud_examen_tiene_variables.tipo_campo')->orderBy('salud_examen_tiene_variables.orden')->get();

              $examenes .= '<b style="width:100%; background: #ddd;">'.$opcion->descripcion.'</b>'.View::make('consultorio_medico.resultado_examen_show_tabla', compact('variables','organos','paciente_id','consulta_id','examen_id'))->render().$vista_formula;// .'<br>'
              $vista_formula = '';
            }
        }
        
        // PROFESIONAL DE LA SALUD
        $raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS nombre_completo';

        $profesional_salud = ProfesionalSalud::leftJoin('core_terceros','core_terceros.id','=','salud_profesionales.core_tercero_id')->select(DB::raw($raw),'salud_profesionales.especialidad','salud_profesionales.numero_carnet_licencia','salud_profesionales.core_tercero_id')->first();

        $firma_autorizada = FirmaAutorizada::get_firma_tercero( $profesional_salud->core_tercero_id );

        $empresa = Empresa::find( Auth::user()->empresa_id );

        return View::make('consultorio_medico.formula_optica_print_pdf', compact('consulta','datos_historia_clinica','examenes','profesional_salud','empresa','formula','firma_autorizada'))->render();
    }


    public function form_agregar_formula_factura()
    {
        $paciente = Paciente::where( 'core_tercero_id', Input::get('core_tercero_id') )->first();

        if ( !is_null( $paciente ) )
        {
            $consultas = $paciente->consultas;

            $tabla = '<table class="table table-striped">
                        <thead>
                            <th style="display: none;">formula_id</th>
                            <th>Fecha</th>
                            <th>Consulta</th>
                            <th>Formula</th>
                            <th>Exámen</th>
                            <th>Seleccionar</th>
                        </thead>
                        <tbody>';

            foreach ($consultas as $una_consulta)
            {
                foreach ( $una_consulta->formulas as $una_formula )
                {
                    foreach ( $una_formula->examenes as $un_examen )
                    {
                        $btn_ver_examen = '<button class="btn btn-default btn-xs btn_ver_examen" data-paciente_id="'.$una_consulta->paciente_id.'" data-consulta_id="'.$una_consulta->id.'" data-examen_id="'.$un_examen->id.'" data-examen_descripcion="'.$un_examen->descripcion.'"> <i class="fa fa-eye"></i> '.$un_examen->descripcion.' </button>';

                        $tipo_de_lentes = TipoLente::find( $una_formula->tipo_de_lentes );

                        if ( is_null( $tipo_de_lentes ) )
                        {
                            $tipo_de_lentes = (object)['descripcion'=>''];
                        }

                        $tabla .= '<tr>
                                    <td style="display: none;"><input type="hidden" name="formula_id" value="'.$una_formula->id.'"></td>
                                    <td>'.$una_consulta->fecha.'</td>
                                    <td> #'.$una_consulta->id.'</td>
                                    <td>'.$tipo_de_lentes->descripcion.'</td>
                                    <td>'.$btn_ver_examen.'</td>
                                    <td> <button class="btn btn-success btn-xs btn_confirmar" style="display: inline;"><i class="fa fa-check"></i></button> </td>
                                </tr>';
                    }
                }
            }

            $tabla .= '</tbody> </table>';

            return $tabla;
        }

        return 'no_es_paciente';
    }


    public function enviar_por_email( $formula_id )
    {
        $documento_vista = $this->generar_documento_vista( $formula_id, Input::get('paciente_id'), Input::get('consulta_id') );

        $empresa = Empresa::find( Auth::user()->empresa_id );

        $formula = FormulaOptica::find( $formula_id );

        $paciente = Paciente::find( $formula->paciente_id );

        $tercero = Tercero::find( $paciente->core_tercero_id );

        $asunto = 'Fórmula de Optometría. Paciente: '.$tercero->descripcion.' Historia clínica No. '.$paciente->codigo_historia_clinica;

        $cuerpo_mensaje = 'Saludos, <br><br> Le hacemos llegar su '. $asunto . 
                            ' <br><br> <p style="width: 100%; text-align: center; color: red;"> <i> Su proxima cita de control es en <b> '.$formula->proximo_control . ' </b> </i> </p>'.
                            ' <br><br> Atentamente, <br> '.$empresa->descripcion.
                            '<br> Tel. '.$empresa->telefono1.
                            '<br> Dir. '.$empresa->direccion1.
                            '<br><br> <p style="width: 100%; text-align: center;"> <b> <i> Recuerde que puede consultar su formula en <a href="'.$empresa->pagina_web.'" target="_blank" >'.$empresa->pagina_web . '</a> </i> </b> </p>';

        $vec = EmailController::enviar_por_email_documento( $empresa->descripcion, $tercero->email, $asunto, $cuerpo_mensaje, $documento_vista );

        return redirect( 'consultorio_medico/pacientes/'.$paciente->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( $vec['tipo_mensaje'], $vec['texto_mensaje'] );
    }
}   