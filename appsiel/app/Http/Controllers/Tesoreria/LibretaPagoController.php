<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Tesoreria\RecaudoCxcController;

// Modelos
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;
use App\Matriculas\Curso;

use App\Core\Colegio;
use App\Core\Empresa;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;

use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoRecaudosLibreta;
use App\Tesoreria\TesoPlanPagosEstudiante;
use App\Tesoreria\TesoCuentaBancaria;

use App\Contabilidad\ContabMovimiento;

use App\Ventas\VtasDocEncabezado;

use App\CxC\CxcMovimiento;
use App\Tesoreria\Services\PaymentBookServices;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Khill\Lavacharts\Laravel\LavachartsFacade;

class LibretaPagoController extends ModeloController
{
    protected $total_valor_movimiento = 0;
    protected $saldo = 0;
    protected $j;

    public function actualizar_estado_cartera()
    {
        // 1ro. PROCESO QUE ACTUALIZA LAS CARTERAS, asignando EL ESTADO Vencida
        // Actualizar las cartera con fechas inferior a hoy y con estado distinto a Pagada
        TesoPlanPagosEstudiante::where('fecha_vencimiento','<', date('Y-m-d'))
          ->where('estado','<>', 'Pagada')
          ->update(['estado' => 'Vencida']);
    }

    public function cartera_vencida_estudiantes()
    {
        $this->actualizar_estado_cartera();

        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $curso_id = '';
        $curso_lbl = 'Todos';
        $cursos = Curso::where('id_colegio',$colegio[0]->id)->where('estado','Activo')->get();
        if ( Input::get('curso_id')!==null ) 
        {
            $curso_id = Input::get('curso_id');
            if ( Input::get('curso_id') != '') {
                $curso_lbl = Curso::find(Input::get('curso_id'))->descripcion;
            }                
        }
        $vec2['']='Todos';
        foreach ($cursos as $opcion){
            $vec2[$opcion->id]=$opcion->descripcion;
        }
        $cursos = $vec2;


        if ( Input::get('curso_id') == '') 
        {
            $curso_id = '%%';
        }else{
            $curso_id = Input::get('curso_id');
        }


        // Creación de gráfico de Torta MATRICULAS
        $stocksTable1 = LavachartsFacade::DataTable();
        
        $stocksTable1->addStringColumn('Meses')
                    ->addNumberColumn('Valor');

        // Obtención de datos
        $inv_producto_id = config('matriculas.inv_producto_id_default_matricula');
        $num_mes="01";
        $cartera_matriculas=array();
        for($i=0;$i<12;$i++){
            if (strlen($num_mes)==1) {
                $num_mes="0".$num_mes;
            }
            $cadena="%-".$num_mes."-%";
            $cartera_matriculas[$num_mes] = TesoPlanPagosEstudiante::leftJoin('sga_matriculas','sga_matriculas.id_estudiante','=','teso_cartera_estudiantes.id_estudiante')
                ->where('curso_id','LIKE', $curso_id)
                ->where('teso_cartera_estudiantes.fecha_vencimiento','LIKE',$cadena)
                ->where('teso_cartera_estudiantes.inv_producto_id','=', $inv_producto_id)
                ->where('teso_cartera_estudiantes.estado','=','Vencida')
                ->sum('teso_cartera_estudiantes.saldo_pendiente');

            // Agregar campo a la torta
            $stocksTable1->addRow([ConfiguracionController::nombre_mes($num_mes), (float)$cartera_matriculas[$num_mes]]);

            $num_mes++;
            if($num_mes>=13){
                $num_mes='01';
            }
        }

        $chart1 = LavachartsFacade::PieChart('torta_matriculas', $stocksTable1,[
                'is3D'                  => True,
                'pieSliceText'          => 'value'
            ]);


        // Creación de gráfico de Torta PENSIONES
        $stocksTable = LavachartsFacade::DataTable();
        
        $stocksTable->addStringColumn('Meses')
                    ->addNumberColumn('Valor');

        // Obtención de datos
        $inv_producto_id = config('matriculas.inv_producto_id_default_pension');
        $num_mes="01";
        $cartera_pensiones=array();
        for($i=0;$i<12;$i++){
            if (strlen($num_mes)==1) {
                $num_mes="0".$num_mes;
            }
            $cadena="%-".$num_mes."-%";
            $cartera_pensiones[$num_mes] = TesoPlanPagosEstudiante::leftJoin('sga_matriculas','sga_matriculas.id_estudiante','=','teso_cartera_estudiantes.id_estudiante')
                ->where('curso_id','LIKE', $curso_id)
                ->where('teso_cartera_estudiantes.fecha_vencimiento','LIKE',$cadena)
                ->where('teso_cartera_estudiantes.inv_producto_id','=',$inv_producto_id)
                ->where('teso_cartera_estudiantes.estado','=','Vencida')
                ->sum('teso_cartera_estudiantes.saldo_pendiente');

            // Agregar campo a la torta
            $stocksTable->addRow([ConfiguracionController::nombre_mes($num_mes), (float)$cartera_pensiones[$num_mes]]);

            $num_mes++;
            if($num_mes>=13){
                $num_mes='01';
            }
        }

        $chart = LavachartsFacade::PieChart('torta_pensiones', $stocksTable,[
                'is3D'                  => True,
                'pieSliceText'          => 'value'
            ]);

        

        $miga_pan = [
                ['url'=>'NO','etiqueta'=>'Tesoreria']
            ];

        return view('tesoreria.cartera_vencida_estudiantes',compact('cartera_pensiones','cartera_matriculas','miga_pan','cursos','curso_id'));
    }

    /**
     * Almacenar Cartera de estudiantes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request )
    {
        $matricula_estudiante = Matricula::get_registro_impresion( $request->matricula_id );
        $request['id_estudiante'] = $matricula_estudiante->id_estudiante;

        if (!isset($request['valor_pension_anual'])) {
            $request['valor_pension_anual'] = $request['valor_pension_mensual'] * $request['numero_periodos'];
        }

        // Crear la libreta
        $registro = $this->crear_nuevo_registro( $request );

        /*      SE CREAN LOS REGISTROS DE CARTERA DE ESTUDIANTES (Plan de Pagos)    */
        $obj_libreta = new PaymentBookServices();
        $obj_libreta->create_payment_plan( $registro->id, $request->id_estudiante, $request->valor_matricula, $request->valor_pension_mensual, $request->fecha_inicio, $request->numero_periodos);

        return redirect( 'tesoreria/ver_plan_pagos/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Libreta creada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);
        $recaudos_libreta = TesoRecaudosLibreta::where('id_libreta',$id)->get();

        // Si la libreta ya tiene recaudos, no se puede modificar.
        if ( isset($recaudos_libreta[0]) ) 
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','La Libreta ya tiene pagos aplicados. No puede ser modificada.' );
        }

        $plan_pagos = TesoPlanPagosEstudiante::where('id_libreta',$id)->get();
        $se_puede_editar_libreta = true;
        foreach($plan_pagos as $fila)
        {
            if( !empty( $fila->facturas_estudiantes->toArray() ) )
            {
                return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','La Libreta ya tiene facturas asociadas. No puede ser modificada.' );
            }
        }
        
        $matricula_estudiante = Matricula::get_registro_impresion( $registro->matricula_id );

        if( is_null( $matricula_estudiante->estudiante ) )
        {
            return redirect( 'web?id=3&id_modelo=31' )->with('mensaje_error','La matrícula no tiene un estudiante asociado. Por favor, consulte con el adminitrador del sistema.');
        }

        $lista_campos = ModeloController::get_campos_modelo($modelo,$registro,'edit');

        /*
            Como el select de estudiantes solo muesta los que no tienen libreta, para la edición se coloca al estudiante de la libreta que se está editando
        */
        $lista_campos[0]['opciones'][$matricula_estudiante->id] = $matricula_estudiante->numero_identificacion.' '.$matricula_estudiante->nombre_estudiante.' ('.$matricula_estudiante->nombre_curso.')';

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_miga_pan($modelo,$registro->descripcion);
        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $url_action = 'web/'.$id;
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
        }


        return view('layouts.edit',compact('form_create','miga_pan','registro', 'url_action', 'archivo_js'));
    }

    /**
     * Actualizar cartera de estudiantes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $matricula_estudiante = Matricula::get_registro_impresion( $request->matricula_id );

        $registro = TesoLibretasPago::find($id);

        //Borrar los registros anteriores de cartera asociados a la libreta, para luego crearlos otra vez
        TesoPlanPagosEstudiante::where( 'id_libreta', $id )->delete();

        // Crear nuevamente registros de cartera (Plan de pagos)
        $obj_libreta = new PaymentBookServices();
        $obj_libreta->create_payment_plan( $registro->id, $matricula_estudiante->id_estudiante, $request->valor_matricula, $request->valor_pension_mensual, $request->fecha_inicio, $request->numero_periodos);

        $registro->fill( $request->all() );
        $registro->id_estudiante = $matricula_estudiante->id_estudiante;
        $registro->save();

        return redirect('tesoreria/ver_plan_pagos/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Libreta modificada correctamente.');
    }


    public function imprimir_libreta($id_libreta)
    {
        
        if ($id_libreta!=0) {
            $libreta = TesoLibretasPago::find($id_libreta);
            $sql_registro = $libreta->consultar_un_registro($id_libreta);
            $registro = $sql_registro[0];    
        }else{
            $registro = 0;
        }

        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0]; 

        $empresa = Empresa::find(Auth::user()->empresa_id);
        
        $cuenta = TesoCuentaBancaria::get_cuenta_por_defecto();

        $formato = config('tesoreria.formato_libreta_pago_defecto');

        if( is_null($formato) )
        {
            $formato = 'pdf_libreta';
        }
        
        $view =  View::make('tesoreria.'.$formato, compact('registro','colegio','empresa','cuenta'))->render();

        //crear PDF   echo $view;
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($view);
        return $pdf->stream('libreta_pagos.pdf');
        /**/
    }

    public function hacer_recaudo_cartera($id_cartera)
    {        
        $cartera = TesoPlanPagosEstudiante::find($id_cartera);
        $libreta = TesoLibretasPago::find($cartera->id_libreta);
        $estudiante = Estudiante::find( $libreta->id_estudiante );
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();

        $factura = VtasDocEncabezado::find( Input::get('vtas_doc_encabezado_id') );

        $registro_cxc = CxcMovimiento::where( [ 'core_tipo_transaccion_id' => $factura->core_tipo_transaccion_id, 'core_tipo_doc_app_id' => $factura->core_tipo_doc_app_id, 'consecutivo' => $factura->consecutivo, ] )->get()->first();

        if ( is_null($registro_cxc) )
        {
            return redirect( 'tesoreria/ver_plan_pagos/' . $cartera->id_libreta . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') )->with( 'mensaje_error', 'Factura <b>' . $factura->tipo_documento_app->prefijo . ' ' . $factura->consecutivo . '</b> no tiene registros de CxC.' );
        }
        
        $id_doc = $registro_cxc->id;

        $matricula = Matricula::find( $libreta->matricula_id );

        $curso = Curso::find($matricula->curso_id);

        $codigo_matricula = $matricula->codigo;

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Libretas de pagos'],
                ['url'=>'tesoreria/ver_plan_pagos/'.$libreta->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Plan de pagos'],
                ['url'=>'NO','etiqueta'=>'Hacer Recaudo']
            ];

        return view('tesoreria.hacer_recaudo_cartera',compact('cartera','libreta','estudiante','colegio','miga_pan','codigo_matricula','curso','factura', 'id_doc'));
    }

    // Almacenar documentos de RECAUDO DE CARTERA DE ESTUDANTE
    public function guardar_recaudo_cartera(Request $request)
    {
        $url_id_modelo = $request->url_id_modelo;

        $fecha = $request->fecha_recaudo;

        //$consecutivo_doc_recaudo = $this->get_consecutivo($request->core_empresa_id, $request->core_tipo_doc_app_id);

        // Se REEMPLAZA la fecha en los datos del request
        $datos = array_merge($request->all(),[ 'fecha' => $fecha, 'mi_token'=>'']);

        // Se guarda el recaudo de la libreta
        $recaudo_libreta = TesoRecaudosLibreta::create( $datos );

        $recaudo_libreta->registro_cartera_estudiante->sumar_abono_registro_cartera_estudiante( $request->valor_recaudo );

        $recaudo_libreta->libreta->actualizar_estado();

        // Crear documento de recaudo de Tesorería, con todos sus procesos: teso_doc_encabezados, teso_doc_registros, teso_movimientos, cxc_abonos, cxc_movimientos, contab_movimientos
        $request['fecha'] = $request->fecha_recaudo;
        $request['referencia_tercero_id'] = $request->core_tercero_id;
        $request['consecutivo'] = '';
        $request['estado'] = 'Activo';

        $request['modificado_por'] = '';
        $request['creado_por'] = Auth::user()->email;
        $request['tipo_recaudo_aux'] = '';
        
        $request['lineas_registros'] = '[{"id_doc":"'. $request->id_doc .'","Cliente":"","Documento interno":"","Fecha":"","Fecha vencimiento":"","Valor Documento":"$0,00","Valor pagado":"$0,00","Saldo pendiente":"$00,00","abono":"' . $request->valor_recaudo . '"},{"id_doc":"","Cliente":"","Documento interno":"$0.00","Fecha":"","Fecha vencimiento":"","Valor Documento":"","Valor pagado":"","Saldo pendiente":""}]';

        // Se crea un recaudo de Tesorería con su movimiento y contabilidad
        $request['url_id_modelo'] = 153; // Recaudo de CxC
        $recaudo_cxc = new RecaudoCxcController;
        $registro_recaudo_cxc = $recaudo_cxc->almacenar( $request );

        $recaudo_libreta->consecutivo = $registro_recaudo_cxc->consecutivo;
        $recaudo_libreta->save();

        return redirect( 'tesoreria/ver_plan_pagos/' . $request->id_libreta . '?id=' . $request->url_id . '&id_modelo=' . $url_id_modelo )->with( 'flash_message', 'Recaudo realizado correctamente.' );
    }

    // consultar_recaudos
    public function ver_recaudos($id_libreta)
    {
        $libreta = TesoLibretasPago::find($id_libreta);
        $estudiante = Estudiante::get_datos_basicos( $libreta->id_estudiante );

        $recaudos = TesoRecaudosLibreta::where('id_libreta',$id_libreta)->get();

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Libretas de pagos'],
                ['url'=>'tesoreria/ver_plan_pagos/'.$id_libreta.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Plan de pagos'],
                ['url'=>'NO','etiqueta'=>'Consulta Recaudos']
            ];

        $matricula = Matricula::where('estado','Activo')->where('id_estudiante',$estudiante->id)->get()[0];

        $curso = Curso::find($matricula->curso_id);

        $codigo_matricula = $matricula->codigo;

        $plan_pagos = TesoPlanPagosEstudiante::where('id_libreta',$id_libreta)->get();

        return view('tesoreria.ver_recaudos',compact('libreta','estudiante','recaudos','miga_pan','codigo_matricula','curso','matricula','plan_pagos'));
    }

    public function ver_plan_pagos($id_libreta)
    {
        TesoPlanPagosEstudiante::where('fecha_vencimiento','<', date('Y-m-d'))
                                  ->where('estado','<>', 'Pagada')
                                  ->update(['estado' => 'Vencida']);

        $libreta = TesoLibretasPago::find($id_libreta);
        if( $libreta == null )
        {
            return redirect( 'web?id=3&id_modelo=31' )->with('mensaje_error','Libreta de estudiante no existe. Debe crear una.');
        }

        $matricula_estudiante = Matricula::get_registro_impresion( $libreta->matricula_id );

        if( $matricula_estudiante->estudiante == null )
        {
            return redirect( 'web?id=3&id_modelo=31' )->with('mensaje_error','La matrícula no tiene un estudiante asociado. Por favor, consulte con el adminitrador del sistema.');
        }

        $plan_pagos = TesoPlanPagosEstudiante::where('id_libreta',$id_libreta)->get();
        
        $responsable_financiero = $matricula_estudiante->estudiante->responsable_financiero();
        
        $documentos_anticipos = [];
        if( !is_null($responsable_financiero) )
        {
            $documentos_anticipos = CxcMovimiento::get_documentos_tercero( $responsable_financiero->tercero->id, date('Y-m-d') );
        }            

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Libretas de pagos'],
                ['url'=>'NO','etiqueta'=>'Plan de pagos']
            ];

        return view('tesoreria.ver_plan_pagos', compact( 'matricula_estudiante', 'libreta', 'plan_pagos', 'miga_pan','documentos_anticipos') );
    }

    public function aplicar_descuento( $cartera_id )
    {
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($cartera_id);
        $recaudos_libreta = TesoRecaudosLibreta::where('id_libreta',$cartera_id)->get();

        // Si la libreta ya tiene recaudos, no se puede modificar.
        if ( isset($recaudos_libreta[0]) ) 
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('mensaje_error','La Libreta ya tiene pagos aplicados. No puede ser modificada.' );
        }

        $lista_campos = ModeloController::get_campos_modelo($modelo,$registro,'edit');

        /*
            Como el select de estudiantes solo muesta los que no tienen libreta, para la edición se coloca al estudiante de la libreta que se está editando
        */
        $matricula_estudiante = Matricula::get_registro_impresion( $registro->matricula_id );

        $lista_campos[0]['opciones'][$matricula_estudiante->id] = $matricula_estudiante->numero_identificacion.' '.$matricula_estudiante->nombre_estudiante.' ('.$matricula_estudiante->nombre_curso.')';

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_miga_pan($modelo,$registro->descripcion);

        $url_action = 'web/'.$cartera_id;
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'/'.$cartera_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
        }


        return view('layouts.edit',compact('form_create','miga_pan','registro', 'url_action'));
    }

    public function imprimir_comprobante_recaudo($id_cartera)
    {
        //echo $id;
        $cartera = TesoPlanPagosEstudiante::find($id_cartera);
        $recaudos = TesoRecaudosLibreta::where('id_cartera',$id_cartera)->get();

        //$empresa = Empresa::find(Auth::user()->empresa_id);
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0]; 

        $view =  View::make('tesoreria.pdf_comprobante_recaudo', compact('cartera','recaudos','colegio'))->render();
        $tam_hoja = 'Letter';
        $orientacion='portrait';

        //crear PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
        return $pdf->stream('comprobante_recaudo.pdf');
    }


    // ELIMINAR LIBRETA
    public function eliminar_libreta_pagos($id)
    {
        // Verificación 1: Recaudos
        $cantidad = TesoRecaudosLibreta::where('id_libreta', $id)->count();
        if($cantidad != 0){
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Libreta de pagos NO puede ser eliminada. Tiene recaudos.');
        }

        // Borrar registros contables
        ContabMovimiento::where('detalle_operacion', 'LIKE', '% ID_LIBRETA'.$id.'.%')->delete();
        //Borrar Cartera asociada a la libreta
        TesoPlanPagosEstudiante::where('id_libreta',$id)->delete();
        //Borrar Libreta
        TesoLibretasPago::find($id)->delete();

        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Libreta ELIMINADA correctamente.');
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