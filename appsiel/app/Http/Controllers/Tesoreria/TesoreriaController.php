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
use App\Tesoreria\TesoCarteraEstudiante;
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
        TesoCarteraEstudiante::where('fecha_vencimiento','<', date('Y-m-d'))
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

                $carteras = TesoCarteraEstudiante::get_cartera_estudiantes_curso( Input::get('curso_id'), $fecha_vencimiento, $concepto);
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

    //   GET CAJAS
    public function get_cajas($empresa_id){
        $registros = TesoCaja::where('core_empresa_id',$empresa_id)->get();        
        foreach ($registros as $fila) {
            $vec_m[$fila->id]=$fila->descripcion; 
        } 
        return $vec_m;
    }

    //   GET CUENTAS BANCARIAS
    public function get_cuentas_bancarias($empresa_id){
        $registros = TesoCuentaBancaria::where('core_empresa_id',$empresa_id)->get();
        foreach ($registros as $fila) {
            $vec_m[$fila->id]=$fila->descripcion; 
        } 
        return $vec_m;
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