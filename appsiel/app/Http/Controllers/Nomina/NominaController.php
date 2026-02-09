<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Empresa;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\Services\LiquidacionPorTurnosService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel as Excel;

class NominaController extends TransaccionController
{
    protected $total_devengos_empleado = 0;
    protected $total_deducciones_empleado = 0;
    protected $vec_totales = [];
    protected $pos = 0;
    protected $registros_procesados = 0;
    protected $vec_campos;
    public $encabezado_doc;

    /* 
        7: Tiempo NO Laborado
        1: tiempo laborado
        6: Aux. transporte
        3: cuotas
        4: prestamos
        10: Fondo de solidaridad pensional
        12: Salud Obligatoria
        13: Pensión Obligatoria
        11: ReteFuente
    */
        
    // Nota: el orden de líquidación para 7, 1, 6, 10 7 11 es muy importante
    protected $array_ids_modos_liquidacion_automaticos = [ 7, 1, 6, 3, 4, 10, 12, 13, 11 ];
    //protected $array_ids_modos_liquidacion_automaticos = [ 10 ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $miga_pan = [
                ['url'=>'NO','etiqueta'=>'Nómina']
            ];

        return view( 'nomina.index', compact( 'miga_pan' ) );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'layouts.create', '' );
    }

    /*
        Por cada empleado activo liquida los conceptos automáticos, las cuotas y préstamos
        Además actualiza el total de devengos y deducciones en el documento de nómina
    */
    public function liquidacion($id)
    {
        $this->registros_procesados = 0;

        $usuario = Auth::user();

        $documento = NomDocEncabezado::find($id);

        // Se obtienen los Empleados del documento
        $empleados_documento = $documento->empleados;

        // Guardar los valores para cada empleado 
        foreach ( $empleados_documento as $empleado ) 
        {
            $cant = count( $this->array_ids_modos_liquidacion_automaticos );

            for ( $i=0; $i < $cant; $i++ ) 
            {
                if ( $empleado->clase_contrato == 'por_turnos') {
                    if( (new LiquidacionPorTurnosService())->almacenar_registro_empleado( $empleado, $documento, $usuario ))
                    {
                        $this->registros_procesados++;
                    }
                }else{
                    $this->liquidar_automaticos_empleado( $this->array_ids_modos_liquidacion_automaticos[$i], $empleado, $documento, $usuario);
                }                
            }
        }

        $this->actualizar_totales_documento($id);

        return redirect( 'nomina/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( 'flash_message','Liquidación realizada correctamente. Se procesaron '.$this->registros_procesados.' registros.' );
    }

    /*
        Recibe doc. de nómina, al empleado y el modo de liquidación para calcular el valor de devengo o deducción de cada concepto
    */
    public function liquidar_automaticos_empleado( $modo_liquidacion_id, $empleado, $documento_nomina, $usuario )
    {
        $conceptos_automaticos = NomConcepto::where('estado','Activo')->where('modo_liquidacion_id', $modo_liquidacion_id)->get();

        foreach ( $conceptos_automaticos as $concepto )
        {
            $cant = 0;
            if ( $modo_liquidacion_id != 7 ) // Si no es TNL. Pueden haber varios registros de estos conceptos en el mismo Doc.
            {
                // Se valida si ya hay una liquidación previa del concepto en ese documento
                $cant = NomDocRegistro::where( 'nom_doc_encabezado_id', $documento_nomina->id)
                                        ->where('nom_contrato_id', $empleado->id)
                                        ->where('nom_concepto_id', $concepto->id)
                                        ->count();
            }
                

            if ( $cant != 0 ) 
            {
                continue;
            }

            // Se llama al subsistema de liquidación
            $liquidacion = new LiquidacionConcepto( $concepto->id, $empleado, $documento_nomina);

            $valores = $liquidacion->calcular( $concepto->modo_liquidacion_id );

            foreach( $valores as $registro )
            {
                $cantidad_horas = 0;
                if( isset($registro['cantidad_horas'] ) )
                {
                    $cantidad_horas = $registro['cantidad_horas'];
                }

                if( ( $registro['valor_devengo'] + $registro['valor_deduccion']  + $cantidad_horas ) != 0 )
                {
                    $registro['valor_devengo'] = round( $registro['valor_devengo'], 0);
                    $registro['valor_deduccion'] = round( $registro['valor_deduccion'], 0);
                    $this->almacenar_linea_registro_documento( $documento_nomina, $empleado, $concepto, $registro, $usuario);

                    $this->registros_procesados++;
                }
            }            
        } // Fin Por cada concepto
    }

    
    public function liquidacion_sp($id)
    {
        $this->registros_procesados = 0;

        $usuario = Auth::user();

        $core_empresa_id = $usuario->empresa_id;

        $documento = NomDocEncabezado::find($id);

        // Se obtienen los Empleados del documento
        $empleados_documento = $documento->empleados;

        /**
         * 10: Fondo de solidaridad pensional
         * 12: Salud Obligatoria
         * 13: Pensión Obligatoria
         */
        $array_ids_modos_liquidacion_automaticos = [ 10, 12, 13];
        
        // Guardar los valores para cada empleado 
        foreach ( $empleados_documento as $empleado ) 
        {
            $cant = count( $array_ids_modos_liquidacion_automaticos );

            for ( $i=0; $i < $cant; $i++ ) 
            {                
                $this->liquidar_automaticos_empleado( $array_ids_modos_liquidacion_automaticos[$i], $empleado, $documento, $usuario);
            }
        }

        $this->actualizar_totales_documento($id);

        return redirect( 'nomina/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( 'flash_message','Liquidación realizada correctamente. Se procesaron '.$this->registros_procesados.' registros.' );
    }

    public function almacenar_linea_registro_documento($documento_nomina, $empleado, $concepto, $registro, $usuario)
    {
        NomDocRegistro::create(
                                    ['nom_doc_encabezado_id' => $documento_nomina->id ] + 
                                    ['fecha' => $documento_nomina->fecha] + 
                                    ['core_empresa_id' => $documento_nomina->core_empresa_id] +  
                                    ['nom_concepto_id' => $concepto->id ] + 
                                    ['core_tercero_id' => $empleado->core_tercero_id ] + 
                                    ['nom_contrato_id' => $empleado->id ] + 
                                    ['estado' => 'Activo'] + 
                                    ['creado_por' => $usuario->email] + 
                                    ['modificado_por' => '']+ 
                                    $registro
                                );
    }

    /**
     * Muestra un documento de liquidación con sus registros
     */
    public function show($encabezado_doc_id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        $reg_anterior = NomDocEncabezado::where('id', '<', $encabezado_doc_id)->max('id');
        $reg_siguiente = NomDocEncabezado::where('id', '>', $encabezado_doc_id)->min('id');
        
        $encabezado_doc =  NomDocEncabezado::get_un_registro( $encabezado_doc_id );

        $empleados = $encabezado_doc->empleados()->with('tercero')->get();

        $conceptos = $encabezado_doc->conceptos_liquidados();

        $totales = $this->construir_totales_documento($encabezado_doc_id);
        $totales_por_empleado_concepto = $totales['totales_por_empleado_concepto'];
        $totales_por_empleado = $totales['totales_por_empleado'];
        $totales_por_concepto = $totales['totales_por_concepto'];

        $miga_pan = [
                  ['url'=>'nomina?id='.Input::get('id'),'etiqueta'=>'Nómina'],
                  ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                  ['url'=>'NO','etiqueta' => 'Consulta' ]
              ];

        // Para el modelo relacionado: Empleados
        $modelo_crud = new ModeloController;
        $respuesta = $modelo_crud->get_tabla_relacionada($modelo, $encabezado_doc);

        $tabla = $respuesta['tabla'];
        $opciones = $respuesta['opciones'];
        $registro_modelo_padre_id = $respuesta['registro_modelo_padre_id'];
        $titulo_tab = $respuesta['titulo_tab'];

        $empresa = Empresa::find( $encabezado_doc->core_empresa_id);
        $ciudad = $empresa->ciudad->descripcion;

        $descripcion_transaccion = $encabezado_doc->tipo_documento_app->descripcion;

        $registros_contabilidad = $encabezado_doc->get_movimiento_contable();

        return view( 'nomina.show', compact( 'reg_anterior', 'reg_siguiente', 'miga_pan', 'empleados', 'conceptos', 'encabezado_doc', 'encabezado_doc_id', 'tabla', 'opciones', 'registro_modelo_padre_id', 'titulo_tab', 'empresa', 'ciudad', 'descripcion_transaccion', 'registros_contabilidad', 'totales_por_empleado_concepto', 'totales_por_empleado', 'totales_por_concepto' ) ); 

    }


    public function nomina_print($id)
    {
      $view_pdf = $this->vista_preliminar($id,'imprimir');

      $tam_hoja = 'folio';
      $orientacion='landscape';
      $pdf = App::make('dompdf.wrapper');
      $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);
      return $pdf->stream('nomina'.$this->encabezado_doc->documento_app.'.pdf');
    }


    // Generar vista para SHOW o IMPRIMIR
    public function vista_preliminar( $encabezado_doc_id, $vista )
    {
        $this->encabezado_doc =  NomDocEncabezado::get_un_registro($encabezado_doc_id);

        $empleados = $this->encabezado_doc->empleados()->with('tercero')->get();

        $conceptos = $this->encabezado_doc->conceptos_liquidados();

        $totales = $this->construir_totales_documento($encabezado_doc_id);
        $totales_por_empleado_concepto = $totales['totales_por_empleado_concepto'];
        $totales_por_empleado = $totales['totales_por_empleado'];
        $totales_por_concepto = $totales['totales_por_concepto'];

        $tabla = View::make( 'nomina.incluir.tabla_registros_documento', compact( 'empleados', 'conceptos', 'encabezado_doc_id', 'totales_por_empleado_concepto', 'totales_por_empleado', 'totales_por_concepto' ) )->render();

        // DATOS ADICIONALES
        $tipo_doc_app = TipoDocApp::find($this->encabezado_doc->core_tipo_doc_app_id);
        $descripcion_transaccion = $tipo_doc_app->descripcion;

        $elaboro = $this->encabezado_doc->creado_por;
        $empresa = Empresa::find($this->encabezado_doc->core_empresa_id);
        $ciudad = DB::table('core_ciudades')
              ->where('id','=',$empresa->codigo_ciudad)
              ->value('descripcion');

        $encabezado_doc = $this->encabezado_doc;

        $firmas = '';
        if(Input::get('formato_impresion_id') == 2)
        {
            $view_1 = View::make('nomina.incluir.encabezado_transaccion2',compact('encabezado_doc','descripcion_transaccion','empresa','vista','ciudad') )->render();

            $view_pdf = $view_1.$tabla.$firmas.'<div class="page-break"></div>';
        }else{
            $view_1 = View::make('nomina.incluir.encabezado_transaccion',compact('encabezado_doc','descripcion_transaccion','empresa','vista','ciudad') )->render();

            $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla.$firmas.'<div class="page-break"></div>';    
        }
        
        
        return $view_pdf;
    }

    // Retiro de conceptos con modo liquidacion automatica
    public function retirar_liquidacion($id)
    {
        $documento_nomina = NomDocEncabezado::find( $id );
        $registros_documento = $documento_nomina->registros_liquidacion;

        $liquidacion_turnos_service = new LiquidacionPorTurnosService();

        foreach ( $registros_documento as $registro )
        {
            if ( $registro->concepto != null && $registro->contrato != null )
            {
                if ( in_array( $registro->concepto->modo_liquidacion_id, $this->array_ids_modos_liquidacion_automaticos) )
                {
                    // Se llama al subsistema de liquidación
                    $liquidacion = new LiquidacionConcepto( $registro->concepto->id, $registro->contrato, $documento_nomina);
                    $liquidacion->retirar( $registro->concepto->modo_liquidacion_id, $registro );
                }

                if ( $registro->concepto->id == (int)config('nomina.concepto_pago_turnos') )
                {
                    $liquidacion_turnos_service->retirar_registro_empleado( $registro->contrato, $documento_nomina );
                }
            }   
        }

        $this->actualizar_totales_documento($id);

        return redirect( 'nomina/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( 'mensaje_error','Registros automáticos retirados correctamente.' );
    }

    function actualizar_totales_documento($nom_doc_encabezado_id)
    {
        $documento = NomDocEncabezado::find($nom_doc_encabezado_id);
        $documento->total_devengos = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_devengo');
        $documento->total_deducciones = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_deduccion');
        $documento->save();
    }

    protected function construir_totales_documento($encabezado_doc_id)
    {
        $totales_agrupados = NomDocRegistro::select(
                                    'nom_contrato_id',
                                    'nom_concepto_id',
                                    DB::raw('SUM(valor_devengo) as sum_devengos'),
                                    DB::raw('SUM(valor_deduccion) as sum_deducciones')
                                )
                                ->where('nom_doc_encabezado_id', $encabezado_doc_id)
                                ->groupBy('nom_contrato_id', 'nom_concepto_id')
                                ->get();

        $totales_por_empleado_concepto = [];
        $totales_por_empleado = [];
        $totales_por_concepto = [];

        if ($totales_agrupados->isEmpty()) {
            $registros = NomDocRegistro::where('nom_doc_encabezado_id', $encabezado_doc_id)
                            ->get(['nom_contrato_id', 'nom_concepto_id', 'valor_devengo', 'valor_deduccion']);

            foreach ($registros as $registro) {
                $nom_contrato_id = $registro->nom_contrato_id;
                $nom_concepto_id = $registro->nom_concepto_id;
                $sum_dev = (float)$registro->valor_devengo;
                $sum_ded = (float)$registro->valor_deduccion;

                if (!isset($totales_por_empleado_concepto[$nom_contrato_id])) {
                    $totales_por_empleado_concepto[$nom_contrato_id] = [];
                }

                if (!isset($totales_por_empleado_concepto[$nom_contrato_id][$nom_concepto_id])) {
                    $totales_por_empleado_concepto[$nom_contrato_id][$nom_concepto_id] = [ 'dev' => 0, 'ded' => 0 ];
                }

                $totales_por_empleado_concepto[$nom_contrato_id][$nom_concepto_id]['dev'] += $sum_dev;
                $totales_por_empleado_concepto[$nom_contrato_id][$nom_concepto_id]['ded'] += $sum_ded;

                if (!isset($totales_por_empleado[$nom_contrato_id])) {
                    $totales_por_empleado[$nom_contrato_id] = [ 'dev' => 0, 'ded' => 0 ];
                }

                $totales_por_empleado[$nom_contrato_id]['dev'] += $sum_dev;
                $totales_por_empleado[$nom_contrato_id]['ded'] += $sum_ded;

                if (!isset($totales_por_concepto[$nom_concepto_id])) {
                    $totales_por_concepto[$nom_concepto_id] = 0;
                }

                $totales_por_concepto[$nom_concepto_id] += ($sum_dev + $sum_ded);
            }
        } else {
            foreach ($totales_agrupados as $fila)
            {
                if (!isset($totales_por_empleado_concepto[$fila->nom_contrato_id])) {
                    $totales_por_empleado_concepto[$fila->nom_contrato_id] = [];
                }

                $totales_por_empleado_concepto[$fila->nom_contrato_id][$fila->nom_concepto_id] = [
                    'dev' => (float)$fila->sum_devengos,
                    'ded' => (float)$fila->sum_deducciones
                ];

                if (!isset($totales_por_empleado[$fila->nom_contrato_id])) {
                    $totales_por_empleado[$fila->nom_contrato_id] = [ 'dev' => 0, 'ded' => 0 ];
                }

                $totales_por_empleado[$fila->nom_contrato_id]['dev'] += (float)$fila->sum_devengos;
                $totales_por_empleado[$fila->nom_contrato_id]['ded'] += (float)$fila->sum_deducciones;

                if (!isset($totales_por_concepto[$fila->nom_concepto_id])) {
                    $totales_por_concepto[$fila->nom_concepto_id] = 0;
                }

                $totales_por_concepto[$fila->nom_concepto_id] += ((float)$fila->sum_devengos + (float)$fila->sum_deducciones);
            }
        }

        return compact('totales_por_empleado_concepto', 'totales_por_empleado', 'totales_por_concepto');
    }

    public function get_datos_contrato( $contrato_id )
    {
        return NomContrato::find( $contrato_id );
    }

    // ASIGNACIÓN DE EMPLEADO A UN DOCUMENTO DE LIQUIDACION
    public function guardar_asignacion(Request $request)
    {
        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find($request->url_id_modelo);

        $datos = app($modelo->name_space)->get_datos_asignacion();

        $this->validate($request, ['registro_modelo_hijo_id' => 'required']);

        DB::table($datos['nombre_tabla'])
            ->insert([
                $datos['nombre_columna1'] => $request->nombre_columna1,
                $datos['registro_modelo_padre_id'] => $request->registro_modelo_padre_id,
                $datos['registro_modelo_hijo_id'] => $request->registro_modelo_hijo_id
            ]);

        $documento_nomina = NomDocEncabezado::find( (int)$request->registro_modelo_padre_id );
        if ( $documento_nomina->tipo_liquidacion == 'terminacion_contrato' )
        {
            $empleado = NomContrato::find( (int)$request->registro_modelo_hijo_id );
            $empleado->estado = 'Retirado';
            $empleado->contrato_hasta = $documento_nomina->fecha;
            $empleado->save();
        }            

        return redirect( 'nomina/' . $request->registro_modelo_padre_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion )->with('flash_message', 'Empleado AGREGADO correctamente al documento.');
    }

    // ELIMINACIÓN DE EMPLEADO DE UN DOCUMENTO DE LIQUIDACION
    public function eliminar_asignacion($nom_contrato_id, $nom_doc_encabezado_id, $id_app, $id_modelo_padre)
    {
        $documento_nomina = NomDocEncabezado::find( (int)$nom_doc_encabezado_id );

        if( !empty( $documento_nomina->registros_liquidacion->where('nom_contrato_id',(int)$nom_contrato_id)->all() ) )
        {
            return redirect( 'nomina/' . $nom_doc_encabezado_id . '?id=' . $id_app . '&id_modelo=' . $id_modelo_padre)->with('mensaje_error', 'El empleado no puede ser RETIRADO del documento. Ya tiene registros de conceptos.');
        }

        if ( $documento_nomina->tipo_liquidacion == 'terminacion_contrato' )
        {
            $empleado = NomContrato::find( (int)$nom_contrato_id );
            $empleado->estado = 'Activo';
            $empleado->save();
        }

        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find($id_modelo_padre);
        $datos = app($modelo->name_space)->get_datos_asignacion();

        DB::table($datos['nombre_tabla'])->where($datos['registro_modelo_hijo_id'], '=', $nom_contrato_id)
            ->where($datos['registro_modelo_padre_id'], '=', $nom_doc_encabezado_id)
            ->delete();

        return redirect( 'nomina/' . $nom_doc_encabezado_id . '?id=' . $id_app . '&id_modelo=' . $id_modelo_padre)->with('flash_message', 'Empleado RETIRADO correctamente del documento.');
    }

    public function export_registros_xlsx($encabezado_doc_id)
    {
        $encabezado_doc = NomDocEncabezado::find($encabezado_doc_id);
        if (is_null($encabezado_doc)) {
            return redirect('nomina')->with('mensaje_error', 'Documento no encontrado.');
        }

        $empleados = $encabezado_doc->empleados()->with('tercero')->get();
        $conceptos = $encabezado_doc->conceptos_liquidados();

        $totales = $this->construir_totales_documento($encabezado_doc_id);
        $totales_por_empleado_concepto = $totales['totales_por_empleado_concepto'];
        $totales_por_empleado = $totales['totales_por_empleado'];
        $totales_por_concepto = $totales['totales_por_concepto'];

        $cabeceras = ['No.', 'EMPLEADO', 'C.C.'];
        foreach ($conceptos as $concepto) {
            $cabeceras[] = $concepto->abreviatura;
        }
        $cabeceras[] = 'T. DEVEN.';
        $cabeceras[] = 'T. DEDUCC.';
        $cabeceras[] = 'T. A PAGAR';

        $filas = [];
        $i = 1;
        $total_dev_doc = 0;
        $total_ded_doc = 0;

        foreach ($empleados as $empleado) {
            $fila = [
                $i,
                $empleado->tercero->descripcion,
                $empleado->tercero->numero_identificacion
            ];

            foreach ($conceptos as $concepto) {
                $cell = $totales_por_empleado_concepto[$empleado->id][$concepto->id] ?? ['dev' => 0, 'ded' => 0];
                $fila[] = $cell['dev'] + $cell['ded'];
            }

            $total_dev = $totales_por_empleado[$empleado->id]['dev'] ?? 0;
            $total_ded = $totales_por_empleado[$empleado->id]['ded'] ?? 0;

            $fila[] = $total_dev;
            $fila[] = $total_ded;
            $fila[] = $total_dev - $total_ded;

            $total_dev_doc += $total_dev;
            $total_ded_doc += $total_ded;

            $filas[] = $fila;
            $i++;
        }

        $fila_totales = ['', '', ''];
        foreach ($conceptos as $concepto) {
            $fila_totales[] = $totales_por_concepto[$concepto->id] ?? 0;
        }
        $fila_totales[] = $total_dev_doc;
        $fila_totales[] = $total_ded_doc;
        $fila_totales[] = $total_dev_doc - $total_ded_doc;
        $filas[] = $fila_totales;

        $nombre_archivo = 'registros_liquidacion_' . $encabezado_doc->get_label_documento();
        $nombre_archivo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre_archivo);

        if (ob_get_length()) {
            ob_end_clean();
        }

        return Excel::create($nombre_archivo, function ($excel) use ($cabeceras, $filas) {
            $excel->setTitle('Registros de Liquidacion');
            $excel->sheet('Registros', function ($sheet) use ($cabeceras, $filas) {
                $sheet->setAutoSize(true);
                $sheet->row(1, $cabeceras);
                $sheet->rows($filas);
            });
        })->download('xlsx');
    }
    
}
