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


    public function imprimir_cartera($concepto,$tipo,$id)
    {

        switch ($tipo) {
            case 'mes':
                $cadena="%-".$id."-%";

                if ( Input::get('curso_id') == '') 
                {
                    $curso_id = '%%';
                }else{
                    $curso_id = Input::get('curso_id');
                }

                $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS nombre_completo';

                $carteras = TesoCarteraEstudiante::leftJoin('sga_estudiantes','sga_estudiantes.id','=','teso_cartera_estudiantes.id_estudiante')
                        ->leftJoin('matriculas','matriculas.id_estudiante','=','sga_estudiantes.id')
                        ->leftJoin('sga_cursos','sga_cursos.id','=','matriculas.curso_id')
                        ->where('matriculas.curso_id','LIKE',$curso_id)
                ->where('teso_cartera_estudiantes.fecha_vencimiento','LIKE',$cadena)
                        ->where('teso_cartera_estudiantes.concepto','=',$concepto)
                        ->where('teso_cartera_estudiantes.estado','=','Vencida')
                        ->where('teso_cartera_estudiantes.saldo_pendiente','<>',0)
                        ->select(DB::raw($select_raw),'sga_estudiantes.doc_identidad', 'sga_estudiantes.apellido1', 'teso_cartera_estudiantes.valor_cartera','teso_cartera_estudiantes.valor_pagado','matriculas.codigo AS codigo_matricula','sga_cursos.descripcion AS nom_curso','sga_cursos.codigo AS codigo_curso')
                        ->orderBy('sga_cursos.codigo','ASC')
                        ->orderBy('sga_estudiantes.apellido1','ASC')
                        ->get();
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

        $view =  View::make('tesoreria.pdf_cartera_2', compact('carteras','colegio','concepto','id','empresa'))->render();
        $tam_hoja = 'Letter';
        $orientacion='portrait';

        //crear PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
        //return $pdf->stream();
        return $pdf->download('cartera.pdf');
        //print_r($carteras);
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