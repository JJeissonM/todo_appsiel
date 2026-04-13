<?php

namespace App\Http\Controllers\Contabilidad;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Sistema\Aplicacion;
use App\Core\Tercero;
use App\Core\Empresa;

use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabCuentaGrupo;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabReporteEeff;
use App\Contabilidad\ContabArbolGruposCuenta;
use App\Contabilidad\ClaseCuenta;
use App\Contabilidad\Impuesto;
use App\Contabilidad\Services\ReportsServices;

use App\CxC\CxcDocEncabezado;
use App\CxC\CxcDocRegistro;
use App\CxC\CxcMovimiento;
use App\CxC\CxcEstadoCartera;

use App\PropiedadHorizontal\Propiedad;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Khill\Lavacharts\Laravel\LavachartsFacade;

class ContabReportesController extends Controller
{
    protected $datos = [];
    protected $grupos_cuentas = [];
    protected $total1_reporte = 0;
    protected $total2_reporte = 0;
    protected $lapso1_lbl, $lapso2_lbl, $lapso3_lbl;
    protected $lapso1_ini, $lapso2_ini, $lapso3_ini;
    protected $lapso1_fin, $lapso2_fin, $lapso3_fin;
    protected $tipo_reporte;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
        Método para probar consultas que se usarán en reportes
        Recibe los parámetros por GET
    */
    public function reporte_prueba()
    {
        //
    }

    public function balance_comprobacion()
    {
        $registros = ContabCuenta::where('core_empresa_id','=',Auth::user()->empresa_id)->orderBy('codigo')->get();
        $cuentas['todas'] = '';

        foreach ($registros as $fila)
        {
            $cuentas[$fila->id] = $fila->codigo." ".$fila->descripcion; 
        }

        $registros_c = ContabCuenta::where('core_empresa_id','=',Auth::user()->empresa_id)->groupBy('contab_cuenta_grupo_id')->get();

        $opciones_c['todos'] = '';
        foreach ($registros_c as $campo) {
            $grupo = DB::table('contab_cuenta_grupos')
                ->where( 'id', $campo->contab_cuenta_grupo_id )
                ->value('descripcion');

            $opciones_c[$campo->contab_cuenta_grupo_id] = $grupo;
        }

        $grupos = $opciones_c;

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>'Estados finacieros'],
                ['url'=>'NO','etiqueta'=>'Balance de comprobación']
            ];

        return view('contabilidad.balance_comprobacion',compact('cuentas','grupos','miga_pan'));
    }

    public function contab_ajax_balance_comprobacion(Request $request)
    {
        try {
            $contab_grupo_cuenta_id = $request->has('contab_grupo_cuenta_id') ? $request->contab_grupo_cuenta_id : 'todos';
            $contab_cuenta_id = $request->has('contab_cuenta_id') ? $request->contab_cuenta_id : 'todas';
            $fecha_desde = $request->fecha_desde;
            $fecha_hasta = $request->fecha_hasta;
            $detallar_terceros = $request->detalla_terceros;

            if ( $contab_grupo_cuenta_id != 'todos' && !empty($contab_grupo_cuenta_id) ) {
                $contab_cuenta_id = 'todas';
            }

            $movimientos_cuentas = $this->get_balance_comprobacion_movimientos(
                $fecha_desde,
                $fecha_hasta,
                $detallar_terceros == 'Si',
                $contab_grupo_cuenta_id,
                $contab_cuenta_id
            );

            $vista = View::make( 'contabilidad.formatos.balance_comprobacion_new', compact('movimientos_cuentas','fecha_desde', 'fecha_hasta') )->render();

            if ($request->has('reporte_instancia')) {
                Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
            }

            return $vista;
        } catch (\Throwable $e) {
            Log::error('Error al generar Balance de comprobacion.', [
                'empresa_id' => Auth::user()->empresa_id,
                'user_id' => Auth::user()->id,
                'request' => $request->all(),
                'exception' => $e
            ]);

            return $this->render_ajax_error(
                'No fue posible generar el balance de comprobacion. Detalle tecnico: ' . $e->getMessage()
            );
        }
    }

    public function get_movimientos_cuentas($movimiento_agrupado_por_cuentas, $fecha_desde)
    {
        $movimientos_cuentas = collect([]);

        foreach ($movimiento_agrupado_por_cuentas as $contab_movim) {
            $arr = (object)[];

            $arr->cuenta = $contab_movim->first()->cuenta;

            if ($arr->cuenta == null) {
                continue;
            }

            $arr->tercero = null;
            $arr->saldo_inicial = ContabMovimiento::where([
                                            ['fecha', '<', $fecha_desde],
                                            ['contab_cuenta_id', '=', $arr->cuenta->id]
                                        ])
                                        ->sum('valor_saldo');
            $arr->debitos = $contab_movim->sum('valor_debito');
            $arr->creditos = $contab_movim->sum('valor_credito');
            $arr->saldo_final = $arr->saldo_inicial + $arr->debitos + $arr->creditos;

            $movimientos_cuentas->push($arr);
        }

        return $movimientos_cuentas;
    }

    protected function get_balance_comprobacion_movimientos($fecha_desde, $fecha_hasta, $detallar_terceros, $contab_grupo_cuenta_id = 'todos', $contab_cuenta_id = 'todas')
    {
        $empresa_id = Auth::user()->empresa_id;

        $query = ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                    ->where('contab_movimientos.core_empresa_id', $empresa_id)
                    ->where('contab_movimientos.fecha', '<=', $fecha_hasta);

        if ($contab_cuenta_id != 'todas' && !empty($contab_cuenta_id)) {
            $query->where('contab_movimientos.contab_cuenta_id', $contab_cuenta_id);
        }

        if ($contab_grupo_cuenta_id != 'todos' && !empty($contab_grupo_cuenta_id)) {
            $query->where('contab_cuentas.contab_cuenta_grupo_id', $contab_grupo_cuenta_id);
        }

        $selects = [
            'contab_movimientos.contab_cuenta_id',
            'contab_cuentas.codigo AS cuenta_codigo',
            'contab_cuentas.descripcion AS cuenta_descripcion'
        ];

        if ($detallar_terceros) {
            $query->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id');
            $selects[] = 'contab_movimientos.core_tercero_id';
            $selects[] = 'core_terceros.numero_identificacion AS tercero_numero_identificacion';
            $selects[] = 'core_terceros.descripcion AS tercero_descripcion';
        }

        $query->select($selects)
            ->selectRaw(
                'SUM(CASE WHEN contab_movimientos.fecha < ? THEN contab_movimientos.valor_saldo ELSE 0 END) AS saldo_inicial',
                [$fecha_desde]
            )
            ->selectRaw(
                'SUM(CASE WHEN contab_movimientos.fecha >= ? AND contab_movimientos.fecha <= ? THEN contab_movimientos.valor_debito ELSE 0 END) AS debitos',
                [$fecha_desde, $fecha_hasta]
            )
            ->selectRaw(
                'SUM(CASE WHEN contab_movimientos.fecha >= ? AND contab_movimientos.fecha <= ? THEN contab_movimientos.valor_credito ELSE 0 END) AS creditos',
                [$fecha_desde, $fecha_hasta]
            );

        if ($detallar_terceros) {
            $query->groupBy(
                'contab_movimientos.contab_cuenta_id',
                'contab_cuentas.codigo',
                'contab_cuentas.descripcion',
                'contab_movimientos.core_tercero_id',
                'core_terceros.numero_identificacion',
                'core_terceros.descripcion'
            );
        } else {
            $query->groupBy(
                'contab_movimientos.contab_cuenta_id',
                'contab_cuentas.codigo',
                'contab_cuentas.descripcion'
            );
        }

        $rows = $query->orderBy('contab_cuentas.codigo')->get();

        $movimientos_cuentas = collect([]);
        foreach ($rows as $row) {
            $saldo_inicial = (float)$row->saldo_inicial;
            $debitos = (float)$row->debitos;
            $creditos = (float)$row->creditos;
            $saldo_final = $saldo_inicial + $debitos + $creditos;

            if ($saldo_inicial == 0 && $debitos == 0 && $creditos == 0 && $saldo_final == 0) {
                continue;
            }

            $arr = (object)[];
            $arr->cuenta = (object)[
                'id' => $row->contab_cuenta_id,
                'codigo' => $row->cuenta_codigo,
                'descripcion' => $row->cuenta_descripcion
            ];
            $arr->tercero = null;
            if ($detallar_terceros && !is_null($row->core_tercero_id)) {
                $arr->tercero = (object)[
                    'id' => $row->core_tercero_id,
                    'numero_identificacion' => $row->tercero_numero_identificacion,
                    'descripcion' => $row->tercero_descripcion
                ];
            }
            $arr->saldo_inicial = $saldo_inicial;
            $arr->debitos = $debitos;
            $arr->creditos = $creditos;
            $arr->saldo_final = $saldo_final;

            $movimientos_cuentas->push($arr);
        }

        return $movimientos_cuentas;
    }

    public function get_movimientos_cuentas_por_terceros($movimiento_agrupado_por_cuentas, $fecha_desde)
    {
        $movimientos_cuentas = collect([]);

        foreach ($movimiento_agrupado_por_cuentas as $grupo_cuentas) {

            $cuenta = $grupo_cuentas->first()->cuenta;

            if ($cuenta == null) {
                continue;
            }

            $terceros_con_movim = ContabMovimiento::get()->unique('core_tercero_id');
            
            foreach ($terceros_con_movim as $linea_movim) {
                
                $arr = (object)[];

                $arr->cuenta = $cuenta;

                $tercero = $linea_movim->tercero;

                $arr->tercero = $tercero;

                $arr->saldo_inicial = 0;
                $arr->debitos = 0;
                $arr->creditos = 0;
                if ($tercero != null) {
                    $arr->saldo_inicial = ContabMovimiento::where([
                        ['fecha', '<', $fecha_desde],
                        ['contab_cuenta_id', '=', $cuenta->id],
                        ['core_tercero_id', '=', $tercero->id],
                    ])
                    ->sum('valor_saldo');
                    $arr->debitos = $grupo_cuentas->where('core_tercero_id', $tercero->id)->sum('valor_debito');
                    $arr->creditos = $grupo_cuentas->where('core_tercero_id', $tercero->id)->sum('valor_credito');
                }                

                $arr->saldo_final = $arr->saldo_inicial + $arr->debitos + $arr->creditos;
                
                if ($arr->saldo_final == 0) {
                    continue;
                }

                $movimientos_cuentas->push($arr);
            }            
        }

        return $movimientos_cuentas;
    }

    public function impuestos( Request $request )
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;        
        $nivel_detalle = $request->nivel_detalle;

        // Transacciones de ingresos (23) y devoluciones por ventas (38, 41)
        $ingresos = ContabMovimiento::get_movimiento_impuestos( [23, 38, 41], $fecha_desde, $fecha_hasta, $nivel_detalle );
        
        //$ingresos_vista = View::make( 'contabilidad.incluir.tabla_impuestos' , compact('movimiento') )->render();

        // Transacciones de compras (25, 29) y devoluciones por compras (36, 40)
        $compras = ContabMovimiento::get_movimiento_impuestos( [25, 29, 36, 40], $fecha_desde, $fecha_hasta, $nivel_detalle );
        
        $vista = View::make( 'contabilidad.incluir.tabla_impuestos2' , compact('ingresos','compras') )->render();


        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    /*
        Formulario generacion_eeff
    */
    public function generacion_eeff()
    {
        $registros = ContabReporteEeff::where('core_empresa_id',Auth::user()->empresa_id)->get();
        $reportes[''] = '';
        foreach ($registros as $fila) {
            $reportes[$fila->id] = $fila->descripcion; 
        }

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>'Estados finacieros'],
                ['url'=>'NO','etiqueta'=>'Generación']
            ];

        return view('contabilidad.generacion_eeff',compact('reportes','miga_pan'));
    }

    // Generar PDF
    public function contab_pdf_eeff()
    {
        $reporte_id = Input::get( 'reporte_id' );
        $anio = Input::get( 'lapso1_lbl' );

        $fecha_inicial = Input::get( 'lapso1_ini' );
        $fecha_final = Input::get( 'lapso1_fin' );

        $modalidad_reporte = Input::get( 'modalidad_reporte' ); 
        $detallar_cuentas = Input::get( 'detallar_cuentas' );        

        $tabla = $this->get_vista_eeff( $anio, $fecha_inicial, $fecha_final, $modalidad_reporte, $reporte_id, $detallar_cuentas );

        $empresa = Empresa::find( Auth::user()->empresa_id );

        switch ( $reporte_id )
        {
            case 'balance_general':
                $titulo = 'ESTADO DE SITUACIÓN FINANCIERA';
                break;
            
            default:
                $titulo = 'ESTADO DE RESULTADOS';
                break;
        } 
        
        $encabezado2 = View::make( 'contabilidad.incluir.encabezado_pdf_eeff', compact('empresa','titulo') )->render();

        $vista = View::make( 'layouts.pdf3', [ 'view' => $encabezado2 . $tabla ] )->render();

        $tam_hoja = 'Letter';
        $orientacion='portrait';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($vista)->setPaper($tam_hoja,$orientacion);

        return $pdf->stream('estados_financieros.pdf');
    }

    /*
        contab_auxiliar_por_cuenta
    */
    public function contab_auxiliar_por_cuenta()
    {
        $opciones = ClaseCuenta::get();

        $clases_cuentas[''] = '';
        foreach ($opciones as $opcion) {
            $clases_cuentas[$opcion->id] = $opcion->descripcion;
        }

        $opciones = ContabCuentaGrupo::where('core_empresa_id', Auth::user()->empresa_id)->get();

        $grupo_cuentas[''] = '';
        foreach ($opciones as $opcion) {
            // No mostrar los padres
            if (in_array($opcion->grupo_padre_id, [null,'',0])) {
                continue;
            }
            $grupo_cuentas[$opcion->id] = $opcion->descripcion;
        }

        
        $registros = ContabCuenta::where('core_empresa_id','=',Auth::user()->empresa_id)
                                ->where('estado','Activo')
                                ->orderBy('codigo')
                                ->get();
        $cuentas[''] = '';
        foreach ($registros as $fila) {
            $cuentas[$fila->id]=$fila->codigo." ".$fila->descripcion; 
        }

        // Verificar módulo de propiedad horizontal
        if ( Aplicacion::find(11)->estado == 'Activo'  ) {
            $registros = Propiedad::where('core_empresa_id',Auth::user()->empresa_id)
                               ->orderBy('codigo')->get();
              $opciones[''] = '';
              foreach ($registros as $fila) {
                  $opciones[$fila->id]=$fila->codigo." - ".$fila->nomenclatura; 
              }
            $propiedades = $opciones;
        }else{
            $propiedades = 'NO';
        }
        
        $registros2 = Tercero::where('core_empresa_id','=',Auth::user()->empresa_id)->orderBy('descripcion')->get();
        $terceros[''] = '';
        foreach ($registros2 as $fila) {
            $terceros[$fila->id]=$fila->numero_identificacion." ".$fila->descripcion; 
        }

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>'Informes y listados'],
                ['url'=>'NO','etiqueta'=>'Auxiliar por cuenta']
            ];

        return view('contabilidad.auxiliar_por_cuenta', compact('cuentas','miga_pan','terceros','grupo_cuentas', 'clases_cuentas') );      
    }

    public function contab_ajax_auxiliar_por_cuenta(Request $request)
    {
        $clase_cuenta_id = $request->clase_cuenta_id;
        $grupo_cuenta_id = $request->grupo_cuenta_id;
        $contab_cuenta_id = $request->contab_cuenta_id;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $core_tercero_id = $request->core_tercero_id;

        if ( $clase_cuenta_id == '' )
        {
            $clase_cuenta_id = null;
        }

        if ( $grupo_cuenta_id == '' )
        {
            $grupo_cuenta_id = null;
        }

        if ( $contab_cuenta_id == '' )
        {
            $contab_cuenta_id = null;
        }

        if ( $core_tercero_id == '' )
        {
            $core_tercero_id = null;
        }

        if ( $contab_cuenta_id == null && $core_tercero_id == null && $grupo_cuenta_id == null && $clase_cuenta_id == null)
        {
            return '<h1>Debe ingresar al menos una Clase, un Grupo de cuentas, Cuenta o Tercero</h1>';
        }

        $saldo_inicial = ContabMovimiento::get_saldo_inicial_v2( $fecha_desde, $contab_cuenta_id, $core_tercero_id,$grupo_cuenta_id, $clase_cuenta_id );

        $movimiento_contable = ContabMovimiento::get_movimiento_contable( $fecha_desde, $fecha_hasta, $contab_cuenta_id, $core_tercero_id, $grupo_cuenta_id, $clase_cuenta_id );

        return View::make( 'contabilidad.incluir.tabla_movimiento_contable', compact( 'movimiento_contable','fecha_desde', 'saldo_inicial' ) )->render();
    }

    public function contab_pdf_estados_de_cuentas()
    {
        $contab_cuenta_id = Input::get('contab_cuenta_id');

        $fecha_inicial = Input::get('fecha_inicial');
        $fecha_final = Input::get('fecha_final');
        $estado = '%'.Input::get('estado').'%';

        if ( Input::get('codigo_referencia_tercero') == '') {
          $codigo_referencia_tercero = '%'.Input::get('codigo_referencia_tercero').'%';
          $operador = 'LIKE';
        }else{
          $codigo_referencia_tercero = Input::get('codigo_referencia_tercero');
          $operador = '=';
        }
          
        $numero_identificacion = '%'.Input::get('core_tercero_id').'%';
        
        $core_empresa_id = Auth::user()->empresa_id;   

        $saldo_inicial = ContabMovimiento::get_saldo_inicial($fecha_inicial, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $core_empresa_id );
        
        $movimiento_cuenta = ContabMovimiento::get_movimiento_cuenta($fecha_inicial, $fecha_final, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $core_empresa_id );

        $empresa = Empresa::find(Auth::user()->empresa_id);
        $vista = 'imprimir';

        $view = View::make( 'contabilidad.incluir.contab_estados_de_cuentas_pdf',compact('saldo_inicial','movimiento_cuenta','empresa','vista') );

        $tam_hoja = 'Letter';
        $orientacion='portrait';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);

        return $pdf->download('estado_de_cuentas.pdf');
        
    }

    public function get_saldo_grupo_cuentas_entre_fechas($fecha_ini,$fecha_fin,$grupo_cuenta_id)
    {
        $saldo_inicial = ContabMovimiento::where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.contab_cuenta_grupo_id','=',$grupo_cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'];

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where('contab_movimientos.fecha','>=',$fecha_ini)
                ->where('contab_movimientos.fecha','<=',$fecha_fin)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.contab_cuenta_grupo_id','=',$grupo_cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'] + $saldo_inicial;
    }

    public function get_saldo_cuentas_entre_fechas($fecha_ini,$fecha_fin,$cuenta_id)
    {
        $saldo_inicial = ContabMovimiento::where('contab_movimientos.fecha','<', $fecha_ini)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_movimientos.contab_cuenta_id','=', $cuenta_id )
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ) )
                ->get()
                ->toArray()[0]['valor_saldo'];

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where('contab_movimientos.fecha','>=',$fecha_ini)
                ->where('contab_movimientos.fecha','<=',$fecha_fin)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.id','=',$cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'] + $saldo_inicial;
    }

    // Proceso especial para crear los encabezado y el movimiento de cxc con base en el movimiento contable
    public function proceso_1()
    {
        /*echo "Proceso INACTIVO. Descomentar el código para activarlo.";*/
        
        $core_empresa_id = 5;
        $fecha_inicial = '2012-01-01';
        $fecha_final = '2018-12-31';

        //$contab_cuenta_id = '130505'; // empresa = 3 bambuterra
        $contab_cuenta_id = '13050%';

        $operador = '=';
        $numero_identificacion = '%'.''.'%';

        $inmuebles = Propiedad::leftJoin('core_terceros','core_terceros.id','=','ph_propiedades.core_tercero_id')->where('ph_propiedades.core_empresa_id',$core_empresa_id)->select('ph_propiedades.id AS codigo_referencia_tercero','ph_propiedades.codigo AS inmueble','core_terceros.id AS core_tercero_id','core_terceros.descripcion AS tercero')->get();

        $num_registro = 1;
        foreach ($inmuebles as $un_inmueble) {
            $saldo_inicial = ContabMovimiento::get_saldo_inicial($fecha_inicial, $contab_cuenta_id, $numero_identificacion, $operador, $un_inmueble->codigo_referencia_tercero, $core_empresa_id );
            
            $movimiento_cuenta = ContabMovimiento::get_movimiento_cuenta($fecha_inicial, $fecha_final, $contab_cuenta_id, $numero_identificacion, $operador, $un_inmueble->codigo_referencia_tercero, $core_empresa_id );

            // Crear movimiento de cartera con los saldos pendientes ( Saldo != 0 )
            $tabla = $this->crear_movimiento_cartera($saldo_inicial, $movimiento_cuenta);

            if ( count( $tabla[0] ) > 0 ) {

                echo $num_registro.") Tercero: ".$un_inmueble->tercero.' - '.$un_inmueble->codigo_referencia_tercero.'<br/>';
                echo "Cod. Inmueble: ".$un_inmueble->inmueble.'<br/>';
                for ($i=0; $i < count( $tabla[0] ); $i++) {
                    
                    $encabezado = CxcDocEncabezado::create($tabla[0][$i]);

                    CxcDocRegistro::create( ['cxc_doc_encabezado_id' => $encabezado->id] +
                        ['cxc_motivo_id' => 0] +
                        ['cxc_servicio_id' => 1] + 
                        ['valor_unitario' => $encabezado->valor_total] +
                        ['cantidad' => 1] +
                        ['valor_total' => $encabezado->valor_total] +
                        ['descripcion' => $encabezado->descripcion] +
                        ['estado' => 'Activo'] );                    
                    // Crear movimiento
                    $cxc_movimiento = CxcMovimiento::create($tabla[1][$i]);

                    // Crear Estado de cartera
                    CxcEstadoCartera::crear($cxc_movimiento->id, $cxc_movimiento->fecha, 0, $cxc_movimiento->valor_cartera, $cxc_movimiento->estado, $cxc_movimiento->creado_por, $cxc_movimiento->modificado_por);
                    
                    echo 'Encabezado <br/>';
                    print_r( $tabla[0][$i] );
                    echo '<br/>';

                    echo 'Movimiento <br/>';
                    print_r( $tabla[1][$i] );
                    echo '<br/>';
                }
                $num_registro++;
            }
        }
    }

    public function crear_movimiento_cartera($saldo_inicial, $movimiento_cuenta)
    {
        
        $vector_encabezado = [];
        $vector_movimiento = [];
                
        $total_debito = 0;
        $total_credito = 0;
        $saldo = 0;
        $i = 0;

        $linea = 0;

        for ($i=0; $i < count($movimiento_cuenta) ; $i++) 
        {           

            $debito = $movimiento_cuenta[$i]['debito'];

            // Si se trata de un documento de cartera
            if ( $debito > 0 ) {
                $credito = 0;
                $valor_cartera = $debito;
                $tipo_movimiento = 'Cartera';
            }else{
                // Si es un recaudo, se tomará como un anticipo para luego hacer los cruces manualmente
                // Para anticipos el valor_cartera es negativo
                $debito = 0;
                $credito = $movimiento_cuenta[$i]['credito'];
                $valor_cartera = $movimiento_cuenta[$i]['credito'];
                $tipo_movimiento = 'anticipo-clientes';
            }
            
            $valor_total = $valor_cartera;
            
            $vector_aux = ['core_tipo_transaccion_id' => $movimiento_cuenta[$i]['core_tipo_transaccion_id']] + 
                    ['core_tipo_doc_app_id' => $movimiento_cuenta[$i]['core_tipo_doc_app_id']] +
                    ['consecutivo' => $movimiento_cuenta[$i]['consecutivo']] +
                    ['fecha' => $movimiento_cuenta[$i]['fecha']] +
                    ['fecha_vencimiento' => $movimiento_cuenta[$i]['fecha']] +
                    ['core_empresa_id' => $movimiento_cuenta[$i]['core_empresa_id']] +
                    ['core_tercero_id' => $movimiento_cuenta[$i]['core_tercero_id']] +
                    ['codigo_referencia_tercero' => $movimiento_cuenta[$i]['codigo_referencia_tercero'] ] + 
                    ['creado_por' => 'administrator@appsiel.com.co'] + 
                    ['modificado_por' => 'administrator@appsiel.com.co'];


            $vector_encabezado[$linea] = $vector_aux + 
                    ['tipo_movimiento' => $tipo_movimiento] + 
                    ['documento_soporte' => $movimiento_cuenta[$i]['documento_soporte'] ] + 
                    ['descripcion' => $movimiento_cuenta[$i]['detalle_operacion'] ] + 
                    ['valor_total' => $valor_total] +
                    ['estado' => 'Activo'];


            $vector_movimiento[$linea] = $vector_aux + 
                    ['valor_cartera' => $valor_cartera] +
                    ['estado' => 'Pendiente'] +
                    ['detalle_operacion' => $movimiento_cuenta[$i]['detalle_operacion'] ];


            $saldo = $saldo_inicial + $debito + $credito;

            // Se actualiza el saldo incial para la siguiente línea
            $saldo_inicial = $saldo;

            // Se incrementa el registro de los vectores 
            $linea++;

            // Si el saldo llega a cero, se borra el movimiento hcia atrás (todo lo guardado hasta el momento)
            /*if ( $saldo == 0) {
                unset($vector_aux);
                $vector_encabezado = [];
                $vector_movimiento = [];
                $linea = 0;
            }*/
        }

        return [ $vector_encabezado, $vector_movimiento ];
    }

    public function reasignar_grupos_cuentas_form()
    {
        $empresa_id = Auth::user()->empresa_id;

        $grupos_cuentas = ContabArbolGruposCuenta::orderBy('abuelo_id')
                                                //->orderBy('padre_id')
                                                //->orderBy('hijo_id')
                                                ->get();

        $cuentas = ContabCuenta::where('core_empresa_id', $empresa_id)->orderBy('codigo','ASC')->get();

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>'Listados'],
                ['url'=>'NO','etiqueta'=>'Árbol de grupos de cuentas']
            ];

        return view('contabilidad.reasignar_grupos_cuentas', compact('cuentas', 'grupos_cuentas','miga_pan'));

    }

    public static function get_select_grupo_cuentas( $grupo_id, $cuenta_id )
    {
        $grupos = ContabCuentaGrupo::where('core_empresa_id', Auth::user()->empresa_id)->get();

        foreach ($grupos as $fila)
        {
            $vec[$fila->id] = $fila->descripcion;
        }

        return FormFacade::select('cuenta_id_'.$cuenta_id, $vec, $grupo_id, ['id' => $cuenta_id, 'class' => 'combobox2']);
    }

    public function reasignar_grupos_cuentas_save($cuenta_id, $grupo_id)
    {
        ContabCuenta::where('id', $cuenta_id)->update( ['contab_cuenta_grupo_id' => $grupo_id ]);

        return "  Grupo Actualizado.";
    }

    public function lista_documentos_descuadrados( Request $request )
    {
        try {
            $fecha_desde = $request->fecha_desde;
            $fecha_hasta = $request->fecha_hasta;

            $registros = ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
                            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween('contab_movimientos.fecha', [$fecha_desde, $fecha_hasta])
                            ->select( 
                                DB::raw('SUM(contab_movimientos.valor_saldo) AS suma_saldos'),
                                DB::raw('SUM(contab_movimientos.valor_debito) AS suma_debitos'),
                                DB::raw('SUM(contab_movimientos.valor_credito) AS suma_creditos'),
                                'contab_movimientos.core_tipo_transaccion_id',
                                'contab_movimientos.core_tipo_doc_app_id',
                                'contab_movimientos.consecutivo',
                                DB::raw('MIN(contab_movimientos.fecha) AS fecha'),
                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS documento')
                            )
                            ->groupBy(
                                'contab_movimientos.core_tipo_transaccion_id',
                                'contab_movimientos.core_tipo_doc_app_id',
                                'contab_movimientos.consecutivo',
                                'core_tipos_docs_apps.prefijo'
                            )
                            ->havingRaw('ROUND(SUM(contab_movimientos.valor_saldo), 2) <> 0')
                            ->orderBy('fecha')
                            ->get();
            
            $vista = View::make( 'contabilidad.incluir.listado_documentos_descuadrados', compact('registros') )->render();

            if ($request->has('reporte_instancia')) {
                Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
            }

            return $vista;
        } catch (\Throwable $e) {
            Log::error('Error al generar listado de documentos descuadrados.', [
                'empresa_id' => Auth::user()->empresa_id,
                'user_id' => Auth::user()->id,
                'request' => $request->all(),
                'exception' => $e
            ]);

            return $this->render_ajax_error(
                'No fue posible generar el listado de documentos descuadrados. Detalle tecnico: ' . $e->getMessage()
            );
        }
    }

    public function cuadre_contabilidad_vs_tesoreria( Request $request )
    {
        try {
            $fecha_desde = $request->fecha_desde;
            $fecha_hasta = $request->fecha_hasta;
            $limite_registros = 2000;

            $cuentas_tesoreria = array_unique(array_merge(
                \App\Tesoreria\TesoCaja::whereNotNull('contab_cuenta_id')->groupBy('contab_cuenta_id')->pluck('contab_cuenta_id')->toArray(),
                \App\Tesoreria\TesoCuentaBancaria::whereNotNull('contab_cuenta_id')->groupBy('contab_cuenta_id')->pluck('contab_cuenta_id')->toArray()
            ));

            if (empty($cuentas_tesoreria)) {
                $registros = collect([]);
            } else {
                $registros = ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
                            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                            ->leftJoin('teso_movimientos AS teso_mov_rel', function ($join) {
                                $join->on('teso_mov_rel.core_empresa_id', '=', 'contab_movimientos.core_empresa_id')
                                    ->on('teso_mov_rel.core_tipo_transaccion_id', '=', 'contab_movimientos.core_tipo_transaccion_id')
                                    ->on('teso_mov_rel.core_tipo_doc_app_id', '=', 'contab_movimientos.core_tipo_doc_app_id')
                                    ->on('teso_mov_rel.consecutivo', '=', 'contab_movimientos.consecutivo');
                            })
                            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween('contab_movimientos.fecha', [$fecha_desde, $fecha_hasta])
                            ->whereIn('contab_movimientos.contab_cuenta_id', $cuentas_tesoreria)
                            ->where(function ($query) {
                                $query->where('contab_movimientos.valor_saldo', '<', -1)
                                      ->orWhere('contab_movimientos.valor_saldo', '>', 1);
                            })
                            ->whereNull('teso_mov_rel.id')
                            ->select(
                                'contab_movimientos.id',
                                DB::raw('CONCAT(contab_movimientos.core_tipo_transaccion_id,contab_movimientos.core_tipo_doc_app_id,contab_movimientos.consecutivo) AS llave_primaria_documento'),
                                'contab_movimientos.core_tipo_transaccion_id',
                                'contab_movimientos.core_tipo_doc_app_id',
                                'contab_movimientos.consecutivo',
                                'contab_movimientos.fecha',
                                'contab_cuentas.codigo AS codigo_cuenta',
                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS documento'),
                                'contab_movimientos.valor_debito',
                                'contab_movimientos.valor_credito',
                                'contab_movimientos.valor_saldo',
                                'contab_movimientos.teso_caja_id',
                                'contab_movimientos.teso_cuenta_bancaria_id',
                                'contab_movimientos.core_tercero_id',
                                'contab_movimientos.contab_cuenta_id',
                                'contab_movimientos.core_empresa_id',
                                'contab_movimientos.detalle_operacion'
                            )
                            ->orderBy('contab_movimientos.fecha')
                            ->limit($limite_registros + 1)
                            ->get();
            }

            $registros_truncados = false;
            if (count($registros) > $limite_registros) {
                $registros = $registros->take($limite_registros);
                $registros_truncados = true;
            }

            $vista = View::make( 'contabilidad.incluir.listado_cuadre_contabilidad_vs_tesoreria', compact('registros', 'registros_truncados', 'limite_registros') )->render();

            if ($request->has('reporte_instancia')) {
                Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
            }

            return $vista;
        } catch (\Throwable $e) {
            Log::error('Error al generar Cuadre Contabilidad vs Tesoreria.', [
                'empresa_id' => Auth::user()->empresa_id,
                'user_id' => Auth::user()->id,
                'request' => $request->all(),
                'exception' => $e
            ]);

            return $this->render_ajax_error(
                'No fue posible generar el cuadre Contabilidad vs Tesoreria. Detalle tecnico: ' . $e->getMessage()
            );
        }
    }

    public static function grafica_riqueza_neta( $fecha_desde, $fecha_hasta )
    {
        $saldo_activos = ContabReportesController::get_saldo_movimiento_por_clase_cuenta( 'activos', $fecha_desde, $fecha_hasta );
        $saldo_pasivos = ContabReportesController::get_saldo_movimiento_por_clase_cuenta( 'pasivos', $fecha_desde, $fecha_hasta );

        $stocksTable = LavachartsFacade::DataTable();
        
        $stocksTable->addStringColumn('rubro')
                    ->addNumberColumn('valor');
        
        $stocksTable->addRow( [ 'Activos', (float)abs( $saldo_activos ) ] );
        $stocksTable->addRow( [ 'Pasivos', (float)abs( $saldo_pasivos ) ] );

        // Creación de gráfico de Torta
        LavachartsFacade::PieChart('Riqueza', $stocksTable);

        return (object)[ 'activos'=> $saldo_activos, 'pasivos'=> $saldo_pasivos, 'patrimonio' => ( $saldo_activos + $saldo_pasivos ) ];
    }

    public static function grafica_flujo_efectivo_neto( $fecha_desde, $fecha_hasta )
    {
        $saldo_ingresos = ContabReportesController::get_saldo_movimiento_por_clase_cuenta( 'ingresos', $fecha_desde, $fecha_hasta );
        $saldo_costos = ContabReportesController::get_saldo_movimiento_por_clase_cuenta( 'costos', $fecha_desde, $fecha_hasta );
        $saldo_gastos = ContabReportesController::get_saldo_movimiento_por_clase_cuenta( 'gastos', $fecha_desde, $fecha_hasta );

        $stocksTable = LavachartsFacade::DataTable();
        
        $stocksTable->addStringColumn('rubro')
                    ->addNumberColumn('valor');
        
        $stocksTable->addRow( [ 'Ingresos', (float)abs( $saldo_ingresos ) ] );
        $stocksTable->addRow( [ 'Costos y Gastos', (float)abs( $saldo_costos ) + (float)abs( $saldo_gastos ) ] );

        // Creación de gráfico de Torta
        LavachartsFacade::PieChart('FlujoNeto', $stocksTable);

        return (object)[ 'ingresos'=> $saldo_ingresos, 'costos_y_gastos'=> ( $saldo_costos + $saldo_gastos ), 'resultado' => ( $saldo_ingresos + ( $saldo_costos + $saldo_gastos ) ) ];
    }

    public static function get_saldo_movimiento_por_clase_cuenta( $descripcion_clase_cuenta, $fecha_desde, $fecha_hasta )
    {

        $clase_cuenta = ClaseCuenta::where( 'descripcion', $descripcion_clase_cuenta)->get()->first();

        return ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                                ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                                ->where('contab_cuentas.contab_cuenta_clase_id', $clase_cuenta->id )
                                ->whereBetween( 'fecha', [ $fecha_desde, $fecha_hasta ] )
                                ->sum('contab_movimientos.valor_saldo');

    }
    
    public function contab_ajax_generacion_eeff(Request $request)
    {
        try {
            $anio = $request->lapso1_lbl;
            $fecha_inicial = $request->lapso1_ini;
            $fecha_final = $request->lapso1_fin;
            $modalidad_reporte = $request->modalidad_reporte;
            $reporte_id = $request->reporte_id;
            $detallar_cuentas = $request->detallar_cuentas;

            return $this->get_vista_eeff($anio, $fecha_inicial, $fecha_final, $modalidad_reporte, $reporte_id, $detallar_cuentas);
        } catch (\Throwable $e) {
            $user = Auth::user();

            Log::error('Error al generar EEFF.', [
                'empresa_id' => is_null($user) ? null : $user->empresa_id,
                'user_id' => is_null($user) ? null : $user->id,
                'request' => $request->all(),
                'exception' => $e
            ]);

            return $this->render_ajax_error(
                'No fue posible generar el reporte. Revise la configuracion de cuentas y grupos contables. Detalle tecnico: ' . $e->getMessage()
            );
        }
    }

    public function get_vista_eeff( $anio, $fecha_inicial, $fecha_final, $modalidad_reporte, $reporte_id, $detallar_cuentas )
    {
        $fecha_inicial = $this->normalizar_fecha_inicial_eeff($modalidad_reporte, $fecha_inicial);

        switch ( $reporte_id )
        {
            case 'balance_general':
                $ids_clases_cuentas = [ 1, 2, 3];
                break;
            
            default:
                $ids_clases_cuentas = [ 4, 5, 6 ];
                break;
        }  

        $obj_repor_serv = new ReportsServices();

        $filas = $this->get_filas_eeff_new( $ids_clases_cuentas, $detallar_cuentas, $fecha_inicial, $fecha_final, $obj_repor_serv);

        switch ( $reporte_id )
        {
            case 'balance_general':
                $gran_total = abs( $obj_repor_serv->totales_clases[ 1 ] ) - abs( $obj_repor_serv->totales_clases[ 2 ] ) - abs( $obj_repor_serv->totales_clases[ 3 ] );
                break;
            
            default:
            $gran_total = abs( $obj_repor_serv->totales_clases[ 4 ] ) - abs( $obj_repor_serv->totales_clases[ 5 ] ) - abs( $obj_repor_serv->totales_clases[ 6 ] );
                break;
        }        

        return View::make('contabilidad.reportes.tabla_eeff', compact('filas', 'anio', 'gran_total') )->render();
    }

    public function get_filas_eeff_new( $ids_clases_cuentas, $detallar_cuentas, $fecha_inicial, $fecha_final, $obj_repor_serv)
    {
        $obj_repor_serv->totales_clases = [ 0, 0, 0, 0, 0, 0, 0 ];
        $estructura = [];
        $cuentas_sin_catalogo = [];
        $cuentas_con_configuracion_invalida = [];

        $movimientos_agrupados = ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                            ->leftJoin('contab_cuenta_clases', 'contab_cuenta_clases.id', '=', 'contab_cuentas.contab_cuenta_clase_id')
                            ->leftJoin('contab_cuenta_grupos AS grupos_hijos', 'grupos_hijos.id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
                            ->leftJoin('contab_cuenta_grupos AS grupos_padres', 'grupos_padres.id', '=', 'grupos_hijos.grupo_padre_id')
                            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween('contab_movimientos.fecha', [ $fecha_inicial, $fecha_final ])
                            ->whereIn('contab_cuentas.contab_cuenta_clase_id', $ids_clases_cuentas)
                            ->select(
                                'contab_movimientos.contab_cuenta_id AS cuenta_id',
                                'contab_cuentas.codigo AS cuenta_codigo',
                                'contab_cuentas.descripcion AS cuenta_descripcion',
                                'contab_cuentas.contab_cuenta_clase_id AS cuenta_clase_id',
                                'contab_cuenta_clases.descripcion AS cuenta_clase_descripcion',
                                'contab_cuentas.contab_cuenta_grupo_id AS cuenta_grupo_hijo_id',
                                'grupos_hijos.descripcion AS cuenta_grupo_hijo_descripcion',
                                'grupos_padres.id AS cuenta_grupo_padre_id',
                                'grupos_padres.descripcion AS cuenta_grupo_padre_descripcion',
                                DB::raw('SUM(contab_movimientos.valor_saldo) AS valor_saldo')
                            )
                            ->groupBy(
                                'contab_movimientos.contab_cuenta_id',
                                'contab_cuentas.codigo',
                                'contab_cuentas.descripcion',
                                'contab_cuentas.contab_cuenta_clase_id',
                                'contab_cuenta_clases.descripcion',
                                'contab_cuentas.contab_cuenta_grupo_id',
                                'grupos_hijos.descripcion',
                                'grupos_padres.id',
                                'grupos_padres.descripcion'
                            )
                            ->orderBy('contab_cuentas.codigo')
                            ->get();

        foreach ($movimientos_agrupados as $linea_movim) {
            if (is_null($linea_movim->cuenta_codigo) || $linea_movim->cuenta_codigo === '') {
                $cuentas_sin_catalogo[$linea_movim->cuenta_id] = $linea_movim->cuenta_id;
                continue;
            }

            if (empty($linea_movim->cuenta_clase_id) || empty($linea_movim->cuenta_grupo_hijo_id) || empty($linea_movim->cuenta_grupo_padre_id)) {
                $cuentas_con_configuracion_invalida[$linea_movim->cuenta_codigo . ' ' . $linea_movim->cuenta_descripcion] = $linea_movim->cuenta_codigo . ' ' . $linea_movim->cuenta_descripcion;
                continue;
            }

            $clase_cuenta_id = (int)$linea_movim->cuenta_clase_id;
            $grupo_padre_id = (int)$linea_movim->cuenta_grupo_padre_id;
            $grupo_hijo_id = (int)$linea_movim->cuenta_grupo_hijo_id;
            $valor_saldo = (float)$linea_movim->valor_saldo;

            if (!isset($estructura[$clase_cuenta_id])) {
                $estructura[$clase_cuenta_id] = [
                    'descripcion' => strtoupper($linea_movim->cuenta_clase_descripcion),
                    'valor' => 0,
                    'grupos_padres' => []
                ];
            }

            if (!isset($estructura[$clase_cuenta_id]['grupos_padres'][$grupo_padre_id])) {
                $estructura[$clase_cuenta_id]['grupos_padres'][$grupo_padre_id] = [
                    'descripcion' => $linea_movim->cuenta_grupo_padre_descripcion,
                    'valor' => 0,
                    'grupos_hijos' => []
                ];
            }

            if (!isset($estructura[$clase_cuenta_id]['grupos_padres'][$grupo_padre_id]['grupos_hijos'][$grupo_hijo_id])) {
                $estructura[$clase_cuenta_id]['grupos_padres'][$grupo_padre_id]['grupos_hijos'][$grupo_hijo_id] = [
                    'descripcion' => $linea_movim->cuenta_grupo_hijo_descripcion,
                    'valor' => 0,
                    'cuentas' => []
                ];
            }

            $estructura[$clase_cuenta_id]['valor'] += $valor_saldo;
            $estructura[$clase_cuenta_id]['grupos_padres'][$grupo_padre_id]['valor'] += $valor_saldo;
            $estructura[$clase_cuenta_id]['grupos_padres'][$grupo_padre_id]['grupos_hijos'][$grupo_hijo_id]['valor'] += $valor_saldo;

            if ($detallar_cuentas) {
                $estructura[$clase_cuenta_id]['grupos_padres'][$grupo_padre_id]['grupos_hijos'][$grupo_hijo_id]['cuentas'][] = (object)[
                    'descripcion' => $linea_movim->cuenta_codigo . ' ' . $linea_movim->cuenta_descripcion,
                    'valor' => $valor_saldo
                ];
            }
        }

        if (!empty($cuentas_sin_catalogo)) {
            throw new \RuntimeException('Hay movimientos con cuentas inexistentes en el catalogo: ' . implode(', ', array_values($cuentas_sin_catalogo)) . '.');
        }

        if (!empty($cuentas_con_configuracion_invalida)) {
            throw new \RuntimeException('Las siguientes cuentas no tienen correctamente asociado un grupo hijo con grupo padre: ' . implode(', ', array_values($cuentas_con_configuracion_invalida)) . '.');
        }

        $filas = [];
        foreach ( $ids_clases_cuentas as $key => $clase_cuenta_id ) {
            if (!isset($estructura[$clase_cuenta_id]) || $estructura[$clase_cuenta_id]['valor'] == 0) {
                continue;
            }

            $valor_clase = (object)[ 
                'descripcion' => $estructura[$clase_cuenta_id]['descripcion'],
                'valor' => $estructura[$clase_cuenta_id]['valor']
            ];
            
            if ( $valor_clase->valor == 0 )
            {
                continue;
            }

            $obj_repor_serv->totales_clases[$clase_cuenta_id] = $valor_clase->valor;

            $filas[] = (object)[
                                'datos_clase_cuenta' => $valor_clase,
                                'datos_grupo_padre' => 0,
                                'datos_grupo_hijo' => 0,
                                'datos_cuenta' => 0
                                ];
            
            foreach ( $estructura[$clase_cuenta_id]['grupos_padres'] as $grupo_padre_id => $grupo_padre_data ) {
                $valor_padre = (object)[ 
                    'descripcion' => $grupo_padre_data['descripcion'],
                    'valor' => $grupo_padre_data['valor']
                ];

                if ( $valor_padre->valor == 0 )
                {
                    continue;
                }

                $filas[] = (object)[
                                    'datos_clase_cuenta' => 0,
                                    'datos_grupo_padre' => $valor_padre,
                                    'datos_grupo_hijo' => 0,
                                    'datos_cuenta' => 0
                                    ];

                foreach ( $grupo_padre_data['grupos_hijos'] as $grupo_hijo_id => $grupo_hijo_data ) {
                    $valor_hijo = (object)[ 
                        'descripcion' => $grupo_hijo_data['descripcion'],
                        'valor' => $grupo_hijo_data['valor']
                    ];

                    if ( $valor_hijo->valor == 0 )
                    {
                        continue;
                    }

                    $filas[] = (object)[
                        'datos_clase_cuenta' => 0,
                        'datos_grupo_padre' => 0,
                        'datos_grupo_hijo' => $valor_hijo,
                        'datos_cuenta' => 0
                        ];
                    
                    foreach ($grupo_hijo_data['cuentas'] as $valor_cuenta) {
                        if( !$detallar_cuentas )
                        {
                            continue;
                        }

                        if ( $valor_cuenta->valor == 0 )
                        {
                            continue;
                        }

                        $filas[] = (object)[
                                                'datos_clase_cuenta' => 0,
                                                'datos_grupo_padre' => 0,
                                                'datos_grupo_hijo' => 0,
                                                'datos_cuenta' => $valor_cuenta
                                                ];
                    }
                }
            }
        }

        return $filas;
    }

    public function get_filas_eeff( $ids_clases_cuentas, $detallar_cuentas, $fecha_inicial, $fecha_final, $obj_repor_serv)
    {
        $obj_repor_serv->clases_cuentas = ClaseCuenta::all();

        $obj_repor_serv->grupos_cuentas = ContabCuentaGrupo::all();

        $obj_repor_serv->cuentas = ContabCuenta::all();        

        $obj_repor_serv->totales_clases = [ 0, 0, 0, 0, 0, 0, 0 ];
        $filas = [];
        foreach ( $ids_clases_cuentas as $key => $clase_cuenta_id )
        {
            $obj_repor_serv->set_mov_clase_cuenta( $fecha_inicial, $fecha_final, $clase_cuenta_id );
            
            // Cada cuenta debe estar, obligatoriamente, asignada a un grupo hijo
            $grupos_invalidos = $obj_repor_serv->validar_grupos_hijos();
            if( !empty( $grupos_invalidos ) )
            {
                dd( 'Las siguientes Cuentas no tienen correctamente asociado un Grupo de cuentas. por favor modifique la Cuenta en los Catálogos para continuar.', $grupos_invalidos );
            }

            $valor_clase = (object)[ 
                'descripcion' => strtoupper( $obj_repor_serv->clases_cuentas->where('id',$clase_cuenta_id)->first()->descripcion ),
                'valor' => $obj_repor_serv->movimiento->where( 'contab_cuenta_clase_id', $clase_cuenta_id )->sum('valor_saldo')
            ];
            
            if ( $valor_clase->valor == 0 )
            {
                continue;
            }

            $obj_repor_serv->totales_clases[$clase_cuenta_id] = $valor_clase->valor;

            $filas[] = (object)[
                                'datos_clase_cuenta' => $valor_clase,
                                'datos_grupo_padre' => 0,
                                'datos_grupo_hijo' => 0,
                                'datos_cuenta' => 0
                                ];
            
            $grupos_padres = $obj_repor_serv->get_ids_grupos_padres( $clase_cuenta_id );
            
            foreach ( $grupos_padres as $key => $grupo_padre_id )
            {
                $valor_padre = $obj_repor_serv->datos_fila_grupo_padre( $grupo_padre_id );

                if ( $valor_padre->valor == 0 )
                {
                    continue;
                }

                $filas[] = (object)[
                                    'datos_clase_cuenta' => 0,
                                    'datos_grupo_padre' => $valor_padre,
                                    'datos_grupo_hijo' => 0,
                                    'datos_cuenta' => 0
                                    ];
                
                $grupos_hijos = $obj_repor_serv->grupos_cuentas->where( 'grupo_padre_id', $grupo_padre_id )->all();
                
                foreach ($grupos_hijos as $grupo_hijo )
                {
                    $valor_hijo = $obj_repor_serv->movimiento->where( 'contab_cuenta_grupo_id', $grupo_hijo->id )->sum('valor_saldo');

                    if ( $valor_hijo == 0 )
                    {
                        continue;
                    }

                    $filas[] = (object)[
                        'datos_clase_cuenta' => 0,
                        'datos_grupo_padre' => 0,
                        'datos_grupo_hijo' => (object)[ 
                                                        'descripcion' => $grupo_hijo->descripcion,
                                                        'valor' => $valor_hijo
                                                    ],
                        'datos_cuenta' => 0
                        ];
                    
                    $cuentas_del_grupo = $obj_repor_serv->cuentas->where( 'contab_cuenta_grupo_id', $grupo_hijo->id );

                    foreach ($cuentas_del_grupo as $cuenta)
                    {
                        if( !$detallar_cuentas )
                        {
                            continue;
                        }
                        
                        $valor_cuenta = $obj_repor_serv->movimiento->where( 'contab_cuenta_id', $cuenta->id )->sum('valor_saldo');

                        if ( $valor_cuenta == 0 )
                        {
                            continue;
                        }

                        $filas[] = (object)[
                                                'datos_clase_cuenta' => 0,
                                                'datos_grupo_padre' => 0,
                                                'datos_grupo_hijo' => 0,
                                                'datos_cuenta' => (object)[ 
                                                                            'descripcion' => $cuenta->codigo . ' ' . $cuenta->descripcion,
                                                                            'valor' => $valor_cuenta
                                                                        ]
                                                ];
                    }
                }
            }
        }

        return $filas;
    }
    
    public function taxes_general_report( Request $request )
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $arr_trasacciones_ventas = [23,44,47,49,50,52];
        $arr_trasacciones_devoluciones_ventas = [41,38,53,54];
        if ((int)$request->mostrar_solo_facturacion_electronica) {
            $arr_trasacciones_ventas = [50,52];
            $arr_trasacciones_devoluciones_ventas = [53,54];
        }
        $reports_list = [
            (object)[
                'title' => 'Impuestos en ventas (generados)',
                'arr_transactions_types' => $arr_trasacciones_ventas,
                'campo_filtrar_ctas' => $this->get_impuesto_account_ids('cta_ventas_id')
            ],
            (object)[
                'title' => 'Impuestos por devoluciones en ventas',
                'arr_transactions_types' => $arr_trasacciones_devoluciones_ventas,
                'campo_filtrar_ctas' => $this->get_impuesto_account_ids('cta_ventas_devol_id')
            ],
            (object)[
                'title' => 'Impuestos en compras (descontables)',
                'arr_transactions_types' => [25,29,48],
                'campo_filtrar_ctas' => $this->get_impuesto_account_ids('cta_compras_id')
            ],
            (object)[
                'title' => 'Impuestos por devoluciones en compras',
                'arr_transactions_types' => [36,40],
                'campo_filtrar_ctas' => $this->get_impuesto_account_ids('cta_compras_devol_id')
            ]
       ];

       $vista = '<table><tr><td>';
       foreach ($reports_list as $report) {
            $group_taxes = $this->get_group_taxes_report(
                $report->arr_transactions_types,
                $report->campo_filtrar_ctas,
                $fecha_desde,
                $fecha_hasta
            );
            $title = $report->title;

            $vista .= View::make( 'contabilidad.incluir.listado_iva_por_tasas', compact('group_taxes','title') )->render();
       }

       $vista .='</td></tr></table>';

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    public function tax_reporting_by_third_parties( Request $request )
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $reports_list = [
            'sales_taxes' => (object)[
                'title' => 'Impuestos en ventas (generados)',
                'arr_transactions_types' => [23,44,49,50,52],
                'campo_filtrar_ctas' => $this->get_impuesto_account_ids('cta_ventas_id')
            ],
            'sales_return_taxes' => (object)[
                'title' => 'Impuestos por devoluciones en ventas',
                'arr_transactions_types' => [41,38,53,54],
                'campo_filtrar_ctas' => $this->get_impuesto_account_ids('cta_ventas_devol_id')
            ],
            'purchases_taxes' => (object)[
                'title' => 'Impuestos en compras (descontables)',
                'arr_transactions_types' => [25,29,48],
                'campo_filtrar_ctas' => $this->get_impuesto_account_ids('cta_compras_id')
            ],
            'purchases_return_taxes' => (object)[
                'title' => 'Impuestos por devoluciones en compras',
                'arr_transactions_types' => [36,40],
                'campo_filtrar_ctas' => $this->get_impuesto_account_ids('cta_compras_devol_id')
            ]
        ];

        $params = $reports_list[$request->tax_report_type];

        $vista = '<table><tr><td>';
        $group_taxes_third_parties = $this->get_group_taxes_by_third_party_report(
            $params->arr_transactions_types,
            $params->campo_filtrar_ctas,
            $fecha_desde,
            $fecha_hasta
        );

        $title = $params->title;

        $vista .= View::make( 'contabilidad.incluir.listado_iva_por_terceros', compact('group_taxes_third_parties','title') )->render();

        $vista .='</td></tr></table>';

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    public function get_totals_by_tax($lines, $column_to_group, $column_method_name)
    {
        $grouped_data = $lines->groupBy($column_to_group)->map(function ($row) use ($column_method_name) {
            return  [
                'group' => $row->first()->$column_method_name,
                'valor_impuesto' => $row->sum('valor_saldo'),
                'impuesto' => $row->first()->impuesto
            ];
        });

        $arr_grouped_data = [];
        foreach ($grouped_data as $line) {

            if ($line['group'] == null) {
                continue;
            }
            
            $valor_impuesto = abs((float)$line['valor_impuesto']);
            $tasa = (float)$line['group'] / 100;
            $base_impuesto = 0;
            if ($tasa != 0) {
                $base_impuesto = $valor_impuesto / $tasa;
            }
            
            $arr_grouped_data[] = [
                'group' => $line['impuesto']->tax_category . ' ' . $line['group'] . '%',
                'base_impuesto' => $base_impuesto,
                'valor_impuesto' => $valor_impuesto
            ];
            
        }

        return $arr_grouped_data;
    }

    public function get_totales_clase_cuenta($clase_cuenta_id)
    {
        $clase_cuenta = ClaseCuenta::find($clase_cuenta_id);

        $lapso1_ini = Input::get('lapso1_ini');
        $lapso1_fin = Input::get('lapso1_fin');
        $lapso1_ini = $this->normalizar_fecha_inicial_eeff(Input::get('modalidad_reporte'), $lapso1_ini);

        $valor_saldo = ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween('contab_movimientos.fecha', [ $lapso1_ini, $lapso1_fin ])
                            ->where('contab_cuentas.contab_cuenta_clase_id', $clase_cuenta_id)
                            ->sum('contab_movimientos.valor_saldo');

        $arr_ids_grupos_padres = ContabCuentaGrupo::where( [
                                        ['core_empresa_id', '=', Auth::user()->empresa_id],
                                        ['grupo_padre_id', '=', 0],
                                        ['contab_cuenta_clase_id', '=', $clase_cuenta_id]
                                    ] )
                                        ->pluck('id')
                                        ->all();

        return Response::json(
                            [
                                'descripcion' => $clase_cuenta->descripcion,
                                'valor_saldo' => ($valor_saldo == null) ? 0 : $valor_saldo,
                                'lbl_cr' => ($valor_saldo < 0) ? 'CR' : '',
                                'arr_ids_grupos_padres' => $arr_ids_grupos_padres
                            ]
                        );
    }

    public function get_totales_grupo_padre($grupo_padre_id)
    {
        $grupo_padre = ContabCuentaGrupo::find($grupo_padre_id);

        $lapso1_ini = Input::get('lapso1_ini');
        $lapso1_fin = Input::get('lapso1_fin');
        $lapso1_ini = $this->normalizar_fecha_inicial_eeff(Input::get('modalidad_reporte'), $lapso1_ini);

        $arr_ids_grupos_hijos = ContabCuentaGrupo::where( [
                                        ['core_empresa_id', '=', Auth::user()->empresa_id],
                                        ['grupo_padre_id', '=', $grupo_padre_id]
                                    ] )
                                        ->pluck('id')
                                        ->all();

        $valor_saldo = 0;
        if (!empty($arr_ids_grupos_hijos)) {
            $valor_saldo = ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                                ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                                ->whereBetween('contab_movimientos.fecha', [ $lapso1_ini, $lapso1_fin ])
                                ->whereIn('contab_cuentas.contab_cuenta_grupo_id', $arr_ids_grupos_hijos)
                                ->sum('contab_movimientos.valor_saldo');
        }

        return Response::json(
                            [
                                'descripcion' => $grupo_padre->descripcion,
                                'valor_saldo' => ($valor_saldo == null) ? 0 : $valor_saldo,
                                'lbl_cr' => ($valor_saldo < 0) ? 'CR' : '',
                                'arr_ids_grupos_hijos' => $arr_ids_grupos_hijos
                            ]
                        );
    }

    public function get_totales_grupo_hijo($grupo_hijo_id)
    {
        $grupo_hijo = ContabCuentaGrupo::find($grupo_hijo_id);

        $lapso1_ini = Input::get('lapso1_ini');
        $lapso1_fin = Input::get('lapso1_fin');
        $lapso1_ini = $this->normalizar_fecha_inicial_eeff(Input::get('modalidad_reporte'), $lapso1_ini);

        $movimientos_por_cuenta = ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween('contab_movimientos.fecha', [ $lapso1_ini, $lapso1_fin ])
                            ->where('contab_cuentas.contab_cuenta_grupo_id', $grupo_hijo_id)
                            ->select(
                                'contab_movimientos.contab_cuenta_id',
                                DB::raw('SUM(contab_movimientos.valor_saldo) AS valor_saldo')
                            )
                            ->groupBy('contab_movimientos.contab_cuenta_id')
                            ->havingRaw('SUM(contab_movimientos.valor_saldo) <> 0')
                            ->get();

        $valor_saldo = $movimientos_por_cuenta->sum('valor_saldo');
        $arr_ids_cuentas = $movimientos_por_cuenta->pluck('contab_cuenta_id')->all();

        return Response::json(
                            [
                                'descripcion' => $grupo_hijo->descripcion,
                                'valor_saldo' => ($valor_saldo == null) ? 0 : $valor_saldo,
                                'lbl_cr' => ($valor_saldo < 0) ? 'CR' : '',
                                'arr_ids_cuentas' => $arr_ids_cuentas
                            ]
                        );
    }

    public function get_totales_cuenta($cuenta_id)
    {
        $cuenta = ContabCuenta::find($cuenta_id);

        $lapso1_ini = Input::get('lapso1_ini');
        $lapso1_fin = Input::get('lapso1_fin');
        $lapso1_ini = $this->normalizar_fecha_inicial_eeff(Input::get('modalidad_reporte'), $lapso1_ini);

        $valor_saldo = ContabMovimiento::where('core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween( 'fecha', [ $lapso1_ini, $lapso1_fin ] )
                            ->where('contab_cuenta_id', $cuenta_id )
                            ->sum('valor_saldo');

        return Response::json(
                            [
                                'descripcion' => $cuenta->codigo . ' ' . $cuenta->descripcion,
                                'valor_saldo' => ($valor_saldo == null) ? 0 : $valor_saldo,
                                'lbl_cr' => ($valor_saldo < 0) ? 'CR' : ''
                            ]
                        );
    }

    protected function validar_integridad_eeff($obj_repor_serv)
    {
        $cuentas_sin_catalogo = collect($obj_repor_serv->movimiento->all())->filter(function ($linea) {
                                return is_null($linea->codigo) || $linea->codigo === '';
                            })
                            ->pluck('contab_cuenta_id')
                            ->unique()
                            ->values()
                            ->toArray();
        if (!empty($cuentas_sin_catalogo)) {
            throw new \RuntimeException('Hay movimientos con cuentas inexistentes en el catalogo: ' . implode(', ', $cuentas_sin_catalogo) . '.');
        }

        $grupos_invalidos = $obj_repor_serv->validar_grupos_hijos();
        if (!empty($grupos_invalidos)) {
            throw new \RuntimeException('Las siguientes cuentas no tienen correctamente asociado un grupo hijo con grupo padre: ' . implode(', ', $grupos_invalidos) . '.');
        }
    }

    protected function render_ajax_error($message)
    {
        return '<div class="alert alert-danger">' . e($message) . '</div>';
    }

    protected function normalizar_fecha_inicial_eeff($modalidad_reporte, $fecha_inicial)
    {
        if ($modalidad_reporte == 'acumular_movimiento') {
            return '1900-01-01';
        }

        return $fecha_inicial;
    }

    protected function get_impuesto_account_ids($column_name)
    {
        return Impuesto::whereNotNull($column_name)
                    ->groupBy($column_name)
                    ->pluck($column_name)
                    ->toArray();
    }

    protected function get_group_taxes_report($transaction_types, $account_ids, $fecha_desde, $fecha_hasta)
    {
        if (empty($account_ids)) {
            return [];
        }

        $rows = ContabMovimiento::leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'contab_movimientos.impuesto_id')
                    ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                    ->whereIn('contab_movimientos.core_tipo_transaccion_id', $transaction_types)
                    ->whereBetween('contab_movimientos.fecha', [ $fecha_desde, $fecha_hasta ])
                    ->whereIn('contab_movimientos.contab_cuenta_id', $account_ids)
                    ->whereNotNull('contab_movimientos.tasa_impuesto')
                    ->select(
                        'contab_movimientos.tasa_impuesto',
                        'contab_impuestos.tax_category',
                        DB::raw('SUM(contab_movimientos.valor_saldo) AS valor_impuesto')
                    )
                    ->groupBy('contab_movimientos.tasa_impuesto', 'contab_impuestos.tax_category')
                    ->orderBy('contab_movimientos.tasa_impuesto')
                    ->get();

        return $this->format_group_taxes_rows($rows);
    }

    protected function get_group_taxes_by_third_party_report($transaction_types, $account_ids, $fecha_desde, $fecha_hasta)
    {
        if (empty($account_ids)) {
            return [];
        }

        $rows = ContabMovimiento::leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'contab_movimientos.impuesto_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
                    ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
                    ->whereIn('contab_movimientos.core_tipo_transaccion_id', $transaction_types)
                    ->whereBetween('contab_movimientos.fecha', [ $fecha_desde, $fecha_hasta ])
                    ->whereIn('contab_movimientos.contab_cuenta_id', $account_ids)
                    ->whereNotNull('contab_movimientos.tasa_impuesto')
                    ->select(
                        'contab_movimientos.core_tercero_id',
                        'core_terceros.numero_identificacion AS tercero_numero_identificacion',
                        'core_terceros.descripcion AS tercero_descripcion',
                        'contab_movimientos.tasa_impuesto',
                        'contab_impuestos.tax_category',
                        DB::raw('SUM(contab_movimientos.valor_saldo) AS valor_impuesto')
                    )
                    ->groupBy(
                        'contab_movimientos.core_tercero_id',
                        'core_terceros.numero_identificacion',
                        'core_terceros.descripcion',
                        'contab_movimientos.tasa_impuesto',
                        'contab_impuestos.tax_category'
                    )
                    ->orderBy('core_terceros.descripcion')
                    ->orderBy('contab_movimientos.tasa_impuesto')
                    ->get();

        $formatted_rows = [];
        foreach ($rows as $row) {
            if (is_null($row->tasa_impuesto)) {
                continue;
            }

            $valor_impuesto = abs((float)$row->valor_impuesto);
            $tasa = (float)$row->tasa_impuesto / 100;
            $base_impuesto = 0;
            if ($tasa != 0) {
                $base_impuesto = $valor_impuesto / $tasa;
            }

            $formatted_rows[] = (object)[
                'tercero_numero_identificacion' => $row->tercero_numero_identificacion,
                'tercero_descripcion' => $row->tercero_descripcion,
                'group' => $this->build_tax_group_label($row->tax_category, $row->tasa_impuesto),
                'base_impuesto' => $base_impuesto,
                'valor_impuesto' => $valor_impuesto
            ];
        }

        return $formatted_rows;
    }

    protected function format_group_taxes_rows($rows)
    {
        $formatted_rows = [];
        foreach ($rows as $row) {
            if (is_null($row->tasa_impuesto)) {
                continue;
            }

            $valor_impuesto = abs((float)$row->valor_impuesto);
            $tasa = (float)$row->tasa_impuesto / 100;
            $base_impuesto = 0;
            if ($tasa != 0) {
                $base_impuesto = $valor_impuesto / $tasa;
            }

            $formatted_rows[] = [
                'group' => $this->build_tax_group_label($row->tax_category, $row->tasa_impuesto),
                'base_impuesto' => $base_impuesto,
                'valor_impuesto' => $valor_impuesto
            ];
        }

        return $formatted_rows;
    }

    protected function build_tax_group_label($tax_category, $tasa_impuesto)
    {
        return trim($tax_category . ' ' . $tasa_impuesto . '%');
    }
}
