<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;

use App\Http\Controllers\Core\ModeloEavController;

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
    
    public function imprimir_historia_medica_ocupacional( $consulta_id )
    {
        $consulta = ConsultaMedica::find( $consulta_id );
        
        $empresa = Empresa::find( Auth::user()->empresa_id );

        $datos_historia_clinica = Paciente::datos_basicos_historia_clinica( $consulta->paciente_id );

        $ids_modelos_relacionados = [ 237, 238, 239, 240, 241, 286, 287, 9999, 288  ];//];
        $vistas_secciones = '';
        foreach ( $ids_modelos_relacionados as $key => $modelo_id )
        {
            if ( $modelo_id == 9999 )
            {
                $vistas_secciones .= '<br><br><br>' . View::make( 'consultorio_medico.salud_ocupacional.espacio_firmas', compact( 'consulta' ) )->render();
                continue;
            }
            
            $secuencia_campo = 1;
            $modelo_sys = Modelo::find( $modelo_id );
            $modelo_seccion_historia_clinica = app( $modelo_sys->name_space );
            $vistas_secciones .= View::make( $modelo_seccion_historia_clinica->vista_imprimir, compact( 'consulta', 'modelo_sys', 'modelo_seccion_historia_clinica', 'empresa') )->render();
        }
        // Se prepara el PDF
        $tam_hoja = 'Letter';
        $orientacion='portrait';

        $view =  View::make('consultorio_medico.salud_ocupacional.historia_medica_ocupacional_1', compact( 'consulta', 'datos_historia_clinica', 'vistas_secciones', 'empresa'))->render();

        $font_size = 12;

        $vista_pdf = View::make('layouts.pdf3', compact( 'view', 'font_size' ) )->render();

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $vista_pdf )->setPaper($tam_hoja,$orientacion);

        //return $view;
        return $pdf->stream( 'historia_clinica.pdf');//stream();
    }

    public function imprimir_certificado_aptitud( $consulta_id )
    {
        $consulta = ConsultaMedica::find( $consulta_id );
        
        $empresa = Empresa::find( Auth::user()->empresa_id );

        $datos_historia_clinica = Paciente::datos_basicos_historia_clinica( $consulta->paciente_id );

        $ids_modelos_relacionados = [ 286, 287, 9999  ];//];
        $vistas_secciones = '';
        foreach ( $ids_modelos_relacionados as $key => $modelo_id )
        {
            if ( $modelo_id == 9999 )
            {
                $vistas_secciones .= View::make( 'consultorio_medico.salud_ocupacional.espacio_firmas_aptitud', compact( 'consulta' ) )->render();
                continue;
            }
            
            $secuencia_campo = 1;
            $modelo_sys = Modelo::find( $modelo_id );
            $modelo_seccion_historia_clinica = app( $modelo_sys->name_space );
            $vistas_secciones .= View::make( $modelo_seccion_historia_clinica->vista_imprimir_aptitud, compact( 'consulta', 'modelo_sys', 'modelo_seccion_historia_clinica', 'empresa') )->render();
        }
        // Se prepara el PDF
        $tam_hoja = 'Letter';
        $orientacion='portrait';

        $view =  View::make('consultorio_medico.salud_ocupacional.certificado_de_aptitud', compact( 'consulta', 'datos_historia_clinica', 'vistas_secciones', 'empresa'))->render();

        $font_size = 13;

        $vista_pdf = View::make('layouts.pdf3', compact( 'view', 'font_size' ) )->render();

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $vista_pdf )->setPaper($tam_hoja,$orientacion);

        //return $view;
        return $pdf->stream( 'historia_clinica.pdf');//stream();
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