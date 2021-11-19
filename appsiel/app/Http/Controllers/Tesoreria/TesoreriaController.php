<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use DB;
use View;
use Lava;
use Input;


use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Matriculas\Grado;
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;
use App\Matriculas\Curso;

use App\Core\Colegio;
use App\Core\Empresa;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\ConsecutivoDocumento;
use App\Core\Tercero;

use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoRecaudosLibreta;
use App\Tesoreria\TesoPlanPagosEstudiante;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoEntidadFinanciera;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\ContabMovimiento;


class TesoreriaController extends TransaccionController
{
    protected $total_valor_movimiento = 0;
    protected $saldo = 0;
    protected $j;

    public function actualizar_estado_cartera(){
        // 1ro. PROCESO QUE ACTUALIZA LAS CARTERAS, asignando EL ESTADO Vencida
        // Actualizar las cartera con fechas inferior a hoy y con estado distinto a Pagada
        TesoPlanPagosEstudiante::where('fecha_vencimiento','<', date('Y-m-d'))
          ->where('estado','<>', 'Pagada')
          ->update(['estado' => 'Vencida']);
    }


    /**
     * Show the form for creating a new LIBRETA DE PAGOS
     *
     * @return \Illuminate\Http\Response
     */
    public function create($form_create,$miga_pan)
    {
        // Viene de ModeloController
        return view('layouts.create',compact('form_create','miga_pan'));
    }

    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $registro = Estudiante::find($id);
        return view('tesoreria.show',compact('registro'));
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


    public function imprimir_cartera($concepto,$tipo,$fecha_vencimiento)
    {

        switch ($tipo) {
            case 'mes':

                $carteras = TesoPlanPagosEstudiante::get_cartera_estudiantes_curso( Input::get('curso_id'), $fecha_vencimiento, $concepto);
                break;
            case 'estudiante':
                # code...
                break;
            
            default:
                # code...
                break;
        }

        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];

        $empresa = Empresa::find(Auth::user()->empresa_id);

        $view =  View::make('tesoreria.pdf_cartera_2', compact('carteras','colegio','concepto','fecha_vencimiento','empresa'))->render();
        $tam_hoja = 'Letter';
        $orientacion='portrait';

        //crear PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
        //return $pdf->stream();
        return $pdf->download('cartera.pdf');
    }

    

    
    // Parámetro enviados por GET
    public function consultar_motivos()
    {
        $texto_busqueda_codigo = (int)Input::get('texto_busqueda');

        // Si es un texto numérico, $texto_busqueda_codigo arojja un valor númerico, sino arroja cero (0)
        // Si es un string
        if( $texto_busqueda_codigo == 0 )
        {
            $campo_busqueda = 'descripcion';
            $texto_busqueda = '%' . str_replace( " ", "%", Input::get('texto_busqueda') ) . '%';
        }else{
            $campo_busqueda = 'id';
            $texto_busqueda = Input::get('texto_busqueda').'%';
        }

        $texto_busqueda_descripcion = '%'.Input::get('texto_busqueda').'%';

        $datos = TesoMotivo::where('teso_motivos.estado','Activo')
                            ->where('teso_motivos.core_empresa_id', Auth::user()->empresa_id)
                            ->where('teso_motivos.'.$campo_busqueda, 'LIKE', $texto_busqueda)
                            ->select(
                                        'teso_motivos.id',
                                        'teso_motivos.descripcion',
                                        'teso_motivos.movimiento')
                            ->get()
                            ->take(7);

        $html = '<div class="list-group">';
        $es_el_primero = true;
        $ultimo_item = 0;
        $num_item = 1;
        $cantidad_datos = count( $datos->toArray() ); // si datos es null?
        foreach ($datos as $linea) 
        {
            $primer_item = 0;
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
                $primer_item = 1;
            }


            if ( $num_item == $cantidad_datos )
            {
                $ultimo_item = 1;
            }

            $html .= '<a class="list-group-item list-group-item-sugerencia '.$clase.'" data-registro_id="'.$linea->id.
                                '" data-primer_item="'.$primer_item.
                                '" data-accion="na" '.
                                '" data-ultimo_item="'.$ultimo_item; // Esto debe ser igual en todas las busquedas

            $html .=            '" data-tipo_campo="cuenta" ';

            $html .=            '" > '.$linea->descripcion.' ('.$linea->movimiento.')'.' </a>';

            $num_item++;
        }

        // Linea crear nuevo registro
        $modelo_id = 49; // Cuentas contables
        $html .= '<a class="list-group-item list-group-item-sugerencia list-group-item-warning" data-modelo_id="'.$modelo_id.'" data-accion="crear_nuevo_registro" > + Crear nuevo </a>';

        $html .= '</div>';

        return $html;
    }


    //   GET CAJAS
    public function get_cajas_to_select( )
    {
        $registros = TesoCaja::where('estado','Activo')->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $fila)
        {
            $opciones .= '<option value="'.$fila->id.'">'.$fila->descripcion.'</option>';
        }

        return $opciones;
    }


    public function get_ctas_bancarias_to_select( )
    {
        $registros = TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('teso_cuentas_bancarias.estado','Activo')
                            ->select( 
                                        'teso_cuentas_bancarias.id',
                                        'teso_cuentas_bancarias.descripcion',
                                        'teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $fila)
        {
            $opciones .= '<option value="'.$fila->id.'">'.$fila->entidad_financiera.': '.$fila->descripcion.'</option>';
        }

        return $opciones;
    }


    //   GET MOTIVOS DE TESORERIA
    public function ajax_get_motivos($tipo_motivo){
        $registros = TesoMotivo::where('teso_tipo_motivo',$tipo_motivo)
                            ->where('estado','Activo')
                            ->where('core_empresa_id',Auth::user()->empresa_id)
                            ->get();
        $opciones='';                
        foreach ($registros as $campo) {
            $opciones.= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }

    // AUMENTAR EL CONSECUTIVO Y OBTENERLO AUMENTADO
    public function get_consecutivo($core_empresa_id, $core_tipo_doc_app_id)
    {
        // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
        $consecutivo = TipoDocApp::get_consecutivo_actual($core_empresa_id, $core_tipo_doc_app_id) + 1;

        // Se incementa el consecutivo para ese tipo de documento y la empresa
        TipoDocApp::aumentar_consecutivo($core_empresa_id, $core_tipo_doc_app_id);

        return $consecutivo;
    }
}