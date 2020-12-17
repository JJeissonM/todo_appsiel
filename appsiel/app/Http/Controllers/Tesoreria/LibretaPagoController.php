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
use App\Http\Controllers\Tesoreria\RecaudoCxcController;


// Modelos
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;
use App\Matriculas\Curso;

use App\Core\Colegio;
use App\Core\Empresa;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
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

use App\Inventarios\InvProducto;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\Cliente;


use App\CxC\CxcMovimiento;


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
        $stocksTable1 = Lava::DataTable();
        
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

        $chart1 = Lava::PieChart('torta_matriculas', $stocksTable1,[
                'is3D'                  => True,
                'pieSliceText'          => 'value'
            ]);


        // Creación de gráfico de Torta PENSIONES
        $stocksTable = Lava::DataTable();
        
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

        $chart = Lava::PieChart('torta_pensiones', $stocksTable,[
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
        $registro = $this->crear_nuevo_registro( $request );

        /*      SE CREAN LOS REGISTROS DE CARTERA DE ESTUDIANTES (Plan de Pagos)    */
        
        $datos = array_combine( (new TesoPlanPagosEstudiante)->getFillable(), ['','','','','','','',''] );

        // Datos comunes
        $datos['id_libreta'] = $registro->id;
        $datos['id_estudiante'] = $request->id_estudiante;
        $datos['estado'] = "Pendiente";

        // 1. Se agrega el registro de matrícula por pagar en la cartera de estudiantes
        // Datos del concepto de Matrícula
        $datos['inv_producto_id'] = (int)config('matriculas.inv_producto_id_default_matricula');
        $datos['valor_cartera'] = $request->valor_matricula;
        $datos['saldo_pendiente'] = $request->valor_matricula;
        $datos['fecha_vencimiento'] = $request->fecha_inicio;

        $this->almacenar_linea_registro_cartera( $datos );

        // 2. Se agregan los registros de pensiones por pagar
        $fecha = explode("-",$request->fecha_inicio);
        $num_mes = $fecha[1];
        for( $i=0; $i < $request->numero_periodos ; $i++)
        {
            $datos['inv_producto_id'] = (int)config('matriculas.inv_producto_id_default_pension');
            $datos['valor_cartera'] = $request->valor_pension_mensual;
            $datos['saldo_pendiente'] = $request->valor_pension_mensual;
            $datos['fecha_vencimiento'] = $fecha[0].'-'.$num_mes.'-' . $fecha[2];
            $this->almacenar_linea_registro_cartera( $datos );
            $num_mes++;
            if ($num_mes>12)
            {
                $num_mes = 1;
            }
        }

        // Datos del concepto de Pensión (por cada mes)
        $this->almacenar_registros_pension( $datos, $request );

        return redirect( 'tesoreria/ver_plan_pagos/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Libreta creada correctamente.');
    }

    public function almacenar_registros_pension( $datos, $request )
    {
        // Datos del concepto de Pensión (por cada mes)
        $fecha = explode( "-", $request->fecha_inicio);
        $num_mes = $fecha[1];
        $num_anio = $fecha[0];
        for($i=0;$i<$request->numero_periodos;$i++)
        {
            $datos['inv_producto_id'] = config('matriculas.inv_producto_id_default_pension');
            $datos['valor_cartera'] = $request->valor_pension_mensual;
            $datos['saldo_pendiente'] = $request->valor_pension_mensual;
            $datos['fecha_vencimiento'] = $num_anio . '-' . $num_mes . '-' . $fecha[2];

            $this->almacenar_linea_registro_cartera( $datos );

            $num_mes++;
            if ($num_mes>12)
            {
                $num_mes = 1;
                $num_anio++;
            }
        }
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

        $url_action = 'web/'.$id;
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
        }


        return view('layouts.edit',compact('form_create','miga_pan','registro', 'url_action'));
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
        $parametros = config('configuracion'); // Llamar al archivo de configuración del core

        $matricula_estudiante = Matricula::get_registro_impresion( $request->matricula_id );

        $registro = TesoLibretasPago::find($id);

        //Borrar los registros anteriores de cartera asociados a la libreta, para luego crearlos otra vez
        TesoPlanPagosEstudiante::where( 'id_libreta', $id )->delete();

        // Crear nuevamente registros de cartera (Plan de pagos)
        $datos = array_combine( (new TesoPlanPagosEstudiante)->getFillable(), ['','','','','','','',''] );

        // Datos comunes
        $datos['id_libreta'] = $id;
        $datos['id_estudiante'] = $matricula_estudiante->id_estudiante;
        $datos['estado'] = "Pendiente";

        // Datos del concepto de Matrícula
        $datos['inv_producto_id'] = config('matriculas.inv_producto_id_default_matricula');
        $datos['valor_cartera'] = $request->valor_matricula;
        $datos['saldo_pendiente'] = $request->valor_matricula;
        $datos['fecha_vencimiento'] = $request->fecha_inicio;

        $this->almacenar_linea_registro_cartera( $datos );

        // Datos del concepto de Pensión (por cada mes)
        $this->almacenar_registros_pension( $datos, $request );

        $registro->fill( $request->all() );
        $registro->id_estudiante = $matricula_estudiante->id_estudiante;
        $registro->save();

        return redirect('tesoreria/ver_plan_pagos/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Libreta modificada correctamente.');
    }

    public function almacenar_linea_registro_cartera( array $datos )
    {
        $cartera = new TesoPlanPagosEstudiante;
        $cartera->fill( $datos );
        $cartera->save();
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
        $pdf = \App::make('dompdf.wrapper');
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

        $consecutivo_doc_recaudo = $this->get_consecutivo($request->core_empresa_id, $request->core_tipo_doc_app_id);

        // Se REEMPLAZA el conscutivo en los datos del request
        $datos = array_merge($request->all(),['consecutivo' => $consecutivo_doc_recaudo,'fecha' => $fecha, 'mi_token'=>'']);

        // Se guarda el recaudo de la libreta
        TesoRecaudosLibreta::create( $datos );

        $this->actualizar_registro_cartera_estudiante( $request->id_cartera, $request->valor_recaudo );

        // Se verifica si la libreta no tiene cartera pendiente y se inactiva
        $this->actualizar_estado_libreta_pago( $request->id_libreta );

        // Crear documento de recaudo de Tesorería, con todos sus procesos: teso_doc_encabezados, teso_doc_registros, teso_movimientos, cxc_abonos, cxc_movimientos, contab_movimientos
        $request['fecha'] = $request->fecha_recaudo;
        $request['referencia_tercero_id'] = $request->core_tercero_id;
        $request['consecutivo'] = '';
        $request['estado'] = 'Activo';

        $request['modificado_por'] = '';
        $request['creado_por'] = Auth::user()->email;
        //$request['cliente_id'] = Cliente::where( 'core_tercero_id', $request->core_tercero_id )->get()->first()->id;
        $request['tipo_recaudo_aux'] = '';
        
        $request['lineas_registros'] = '[{"id_doc":"'. $request->id_doc .'","Cliente":"","Documento interno":"","Fecha":"2020-10-23","Fecha vencimiento":"","Valor Documento":"$0,00","Valor pagado":"$0,00","Saldo pendiente":"$00,00","abono":"' . $request->valor_recaudo . '"},{"id_doc":"","Cliente":"","Documento interno":"$0.00","Fecha":"","Fecha vencimiento":"","Valor Documento":"","Valor pagado":"","Saldo pendiente":""}]';

        $request['url_id_modelo'] = 153; // Recaudo de CxC
        $recaudo_cxc = new RecaudoCxcController;
        $aux = $recaudo_cxc->store( $request );

        return redirect( 'tesoreria/ver_plan_pagos/' . $request->id_libreta . '?id=' . $request->url_id . '&id_modelo=' . $url_id_modelo )->with( 'flash_message', 'Recaudo realizado correctamente.' );
    }

    public function actualizar_registro_cartera_estudiante( $id_cartera, $valor_recaudo )
    {
        $cartera = TesoPlanPagosEstudiante::find( $id_cartera );
        $valor_pagado = $cartera->valor_pagado + $valor_recaudo;
        $saldo_pendiente = $cartera->saldo_pendiente - $valor_recaudo;
        $estado = $cartera->estado;
        if( $valor_pagado == $cartera->valor_cartera )
        {
            $estado="Pagada";
        }
        $cartera->valor_pagado = $valor_pagado;
        $cartera->saldo_pendiente = $saldo_pendiente;
        $cartera->estado = $estado;
        $cartera->save();
    }

    public function actualizar_estado_libreta_pago( $id_libreta )
    {
        $suma_matriculas = TesoPlanPagosEstudiante::get_total_valor_pagado_concepto( $id_libreta, config('matriculas.inv_producto_id_default_matricula') );
        $suma_pensiones = TesoPlanPagosEstudiante::get_total_valor_pagado_concepto( $id_libreta, config('matriculas.inv_producto_id_default_pension') );
        $total_pagado = $suma_matriculas + $suma_pensiones ;
        $libreta = TesoLibretasPago::find( $id_libreta );
        $total_libreta = $libreta->valor_matricula + $libreta->valor_pension_anual;
        if ( $total_pagado == $total_libreta )
        {
            $libreta->estado = "Inactivo";
            $libreta->save();
        }
    }

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

        return view('tesoreria.ver_recaudos',compact('libreta','estudiante','recaudos','miga_pan','codigo_matricula','curso'));
    }

    public function ver_plan_pagos($id_libreta)
    {
        TesoPlanPagosEstudiante::where('fecha_vencimiento','<', date('Y-m-d'))
                                  ->where('estado','<>', 'Pagada')
                                  ->update(['estado' => 'Vencida']);

        $libreta = TesoLibretasPago::find($id_libreta);

        $matricula_estudiante = Matricula::get_registro_impresion( $libreta->matricula_id );

        $plan_pagos = TesoPlanPagosEstudiante::where('id_libreta',$id_libreta)->get();

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Libretas de pagos'],
                ['url'=>'NO','etiqueta'=>'Plan de pagos']
            ];

        return view('tesoreria.ver_plan_pagos', compact('matricula_estudiante', 'libreta', 'plan_pagos', 'miga_pan') );
    }


    public function aplicar_descuento( $cartera_id )
    {
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);
        $recaudos_libreta = TesoRecaudosLibreta::where('id_libreta',$id)->get();

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

        $url_action = 'web/'.$id;
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
        }


        return view('layouts.edit',compact('form_create','miga_pan','registro', 'url_action'));
    }

    public function imprimir_comprobante_recaudo($id_cartera)
    {
        //echo $id;
        $cartera = TesoPlanPagosEstudiante::find($id_cartera);
        $recaudos = TesoRecaudosLibreta::where('id_cartera',$id_cartera)->get();

        dd( $recaudos );

        //$empresa = Empresa::find(Auth::user()->empresa_id);
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0]; 

        $view =  View::make('tesoreria.pdf_comprobante_recaudo', compact('cartera','recaudos','colegio'))->render();
        $tam_hoja = 'Letter';
        $orientacion='portrait';

        //crear PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
        return $pdf->stream('comprobante_recaudo.pdf');
    }


    public function eliminar_recaudo_libreta($recaudo_id)
    {
        $recaudo = TesoRecaudosLibreta::find($recaudo_id);

        // 1ro. Borrar registros contables
        ContabMovimiento::where('core_empresa_id',Auth::user()->empresa_id)
            ->where('core_tipo_transaccion_id', $recaudo->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $recaudo->core_tipo_doc_app_id)
            ->where('consecutivo', $recaudo->consecutivo)
            ->delete();

        // 2do. Eliminar movimiento de tesorería
        TesoMovimiento::where('core_empresa_id',Auth::user()->empresa_id)
            ->where('core_tipo_transaccion_id', $recaudo->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $recaudo->core_tipo_doc_app_id)
            ->where('consecutivo', $recaudo->consecutivo)
            ->delete();

        // 3ro. Reversar valor que el recaudo descontó en cartera
        // Se Actualiza la cartera del estudiante
        $cartera = TesoPlanPagosEstudiante::find($recaudo->id_cartera);
        $nuevo_valor_pagado = $cartera->valor_pagado - $recaudo->valor_recaudo;
        $saldo_pendiente = $cartera->saldo_pendiente + $recaudo->valor_recaudo;
        $estado = $cartera->estado;
        
        if($nuevo_valor_pagado == $cartera->valor_cartera)
        {
            $estado="Pagada";
        }else{
            $estado="Pendiente";
        }
        
        $cartera->valor_pagado = $nuevo_valor_pagado;
        $cartera->saldo_pendiente = $saldo_pendiente;
        $cartera->estado = $estado;
        $cartera->save();

        // Se verifica si la libreta no tiene cartera pendiente y se inactiva
        $suma_matriculas = TesoPlanPagosEstudiante::get_total_valor_pagado_concepto( $recaudo->id_libreta, config('matriculas.inv_producto_id_default_matricula') );
        $suma_pensiones = TesoPlanPagosEstudiante::get_total_valor_pagado_concepto( $recaudo->id_libreta, config('matriculas.inv_producto_id_default_pension') );

        $total_pagado = $suma_matriculas + $suma_pensiones ;
        $libreta = TesoLibretasPago::find($recaudo->id_libreta);
        $total_libreta = $libreta->valor_matricula + $libreta->valor_pension_anual;
        if ($total_pagado==$total_libreta) 
        {
            $libreta->estado = "Inactivo";
        }else{
            $libreta->estado = "Activo";
        }
        $libreta->save();


        // 4to. Se elimina el recaudo
        $recaudo->delete();

        return redirect('tesoreria/ver_recaudos/'.$recaudo->id_libreta.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('flash_message','Recaudo Eliminado correctamente.');

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