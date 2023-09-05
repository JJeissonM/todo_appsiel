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
        $contab_grupo_cuenta_id = $request->contab_grupo_cuenta_id;
        $contab_cuenta_id = $request->contab_cuenta_id;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;
        
        if ( $contab_grupo_cuenta_id != 'todos' ) {
            $detallar_grupo_cuentas = 1;
            // cuando se escoge un grupo de cuenta, se omite la selección de una cuenta específica
            $contab_cuenta_id = 'todas';
        }
        
        $detallar_grupo_cuentas = $request->detallar_grupo_cuentas;
        $detallar_terceros = $request->detallar_terceros;
        $detallar_documentos = $request->detallar_documentos;

        if ( $contab_cuenta_id == 'todas' ) {
            $cuentas_con_movimiento = ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
                                                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                                                ->select( 
                                                            'contab_cuentas.id',
                                                            'contab_cuentas.codigo',
                                                            'contab_cuentas.descripcion'
                                                        )
                                                ->orderBy('contab_cuentas.contab_cuenta_clase_id')
                                                ->groupBy('contab_movimientos.contab_cuenta_id')
                                                ->get()
                                                ->toArray();

            $vista = View::make( 'contabilidad.formatos.balance_comprobacion_1', compact('cuentas_con_movimiento','fecha_desde', 'fecha_hasta') )->render();

            Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
  
        }else{

        }
   
        return $vista;
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
                $tipo_movimiento = 'Anticipo';
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
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $movimiento = ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
                        ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                        
                        ->select( 
                                    DB::raw('SUM(contab_movimientos.valor_saldo) AS suma_saldos'),
                                    DB::raw('SUM(contab_movimientos.valor_debito) AS suma_debitos'),
                                    DB::raw('SUM(contab_movimientos.valor_credito) AS suma_creditos'),
                                    DB::raw('CONCAT(contab_movimientos.core_tipo_transaccion_id,contab_movimientos.core_tipo_doc_app_id,contab_movimientos.consecutivo) AS llave_primaria_documento'),
                                    'contab_movimientos.core_tipo_transaccion_id',
                                    'contab_movimientos.core_tipo_doc_app_id',
                                    'contab_movimientos.consecutivo',
                                    'contab_movimientos.fecha',
                                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS documento') )
                        ->groupBy('llave_primaria_documento')
                        ->orderBy('contab_movimientos.fecha')
                        ->get();

        $registros = $movimiento->filter(function ($value, $key) {
            return round( $value->suma_saldos ) != 0;
        });
        
        $vista = View::make( 'contabilidad.incluir.listado_documentos_descuadrados', compact('registros') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    public function cuadre_contabilidad_vs_tesoreria( Request $request )
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $cuentas_tesoreria = [];

        $cuentas_tesoreria = array_merge ( $cuentas_tesoreria, \App\Tesoreria\TesoCaja::groupBy('contab_cuenta_id')->get()->pluck('contab_cuenta_id')->toArray() );

        $cuentas_tesoreria = array_merge ( $cuentas_tesoreria, \App\Tesoreria\TesoCuentaBancaria::groupBy('contab_cuenta_id')->get()->pluck('contab_cuenta_id')->toArray() );

        $movimiento = ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
                        ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                        ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                        ->whereIn( 'contab_cuenta_id', $cuentas_tesoreria )
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
                                    'contab_movimientos.detalle_operacion' )
                        ->orderBy('contab_movimientos.fecha')
                        ->get();

        // Se filtran los registros del movimiento contable que no están en el movimiento de tesorería
        $registros = $movimiento->filter(function ($value, $key)
        {
            return \App\Tesoreria\TesoMovimiento::where(
                                                        [ 
                                                            'core_tipo_transaccion_id' => $value->core_tipo_transaccion_id,
                                                            'core_tipo_doc_app_id' => $value->core_tipo_doc_app_id,
                                                            'consecutivo' => $value->consecutivo,
                                                        ]
                                                        )
                                                ->get()->first() == null;
        });

        $vista = View::make( 'contabilidad.incluir.listado_cuadre_contabilidad_vs_tesoreria', compact('registros') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
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
    

    /*
    ** Cada EEFF es un reporte que tiene asociados grupos de cuentas. Se deben asignar GRUPOS PADRES
    ** Los grupos de cuentas estan estructurados en forma de arbol en una tabla de la base de datos. De manera que al asignar un grupo padre al reporte, se traigan todo sus grupos descendientes hasta llegar a las cuentas
    */
    public function contab_ajax_generacion_eeff(Request $request)
    {
        $anio = $request->lapso1_lbl;
        $fecha_inicial = $request->lapso1_ini;
        $fecha_final = $request->lapso1_fin;
        $modalidad_reporte = $request->modalidad_reporte;
        $reporte_id = $request->reporte_id;
        $detallar_cuentas = $request->detallar_cuentas;
        
        echo $this->get_vista_eeff( $anio, $fecha_inicial, $fecha_final, $modalidad_reporte, $reporte_id, $detallar_cuentas );
    }

    public function get_vista_eeff( $anio, $fecha_inicial, $fecha_final, $modalidad_reporte, $reporte_id, $detallar_cuentas )
    {
        if ( $modalidad_reporte == 'acumular_movimiento' )
        {
            $fecha_inicial = '1900-01-01';
        }

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
        $totales_clases = [ 0, 0, 0, 0, 0, 0, 0 ];
        $filas = [];
        foreach ( $ids_clases_cuentas as $key => $clase_cuenta_id )
        {
            $obj_repor_serv->set_mov_clase_cuenta( $fecha_inicial, $fecha_final, $clase_cuenta_id );

            $valor_clase = $obj_repor_serv->datos_clase_cuenta( $clase_cuenta_id );
            
            if ( $valor_clase->valor == 0 )
            {
                continue;
            }

            $totales_clases[$clase_cuenta_id] = $valor_clase->valor;

            $filas[] = (object)[
                                'datos_clase_cuenta' => $valor_clase,
                                'datos_grupo_padre' => 0,
                                'datos_grupo_hijo' => 0,
                                'datos_cuenta' => 0
                                ];
                                
            // Cada cuenta debe estar, obligatoriamente, asignada a un grupo hijo
            $grupos_invalidos = $obj_repor_serv->validar_grupos_hijos( array_keys( $obj_repor_serv->movimiento->groupBy('contab_cuenta_id')->all() ) );
            if( !empty( $grupos_invalidos ) )
            {
                dd( 'Las siguientes Cuentas no tienen correctamente asociado un Grupo de cuentas. por favor modifique la Cuenta en los Catálogos para continuar.', $grupos_invalidos );
            }

            $grupos_hijos = $obj_repor_serv->movimiento->groupBy('contab_cuenta_grupo_id')->all();
            
            $grupos_padres = $obj_repor_serv->get_ids_grupos_padres( array_keys( $grupos_hijos ) );
            //$grupos_padres_clases_cuentas = $obj_repor_serv->get_grupos_padre_de_clase_cuenta( 1 );
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
                
                $grupos_hijos = $obj_repor_serv->get_grupos_hijos( $grupo_padre_id );
                foreach ($grupos_hijos as $grupo_hijo )
                {
                    $valor_hijo = $obj_repor_serv->get_mov_grupo_cuenta( $fecha_inicial, $fecha_final, $grupo_hijo->id )->sum('valor_saldo');

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
                    
                    $cuentas_del_grupo = $obj_repor_serv->get_cuentas_del_grupo( $grupo_hijo->id );

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

        switch ( $reporte_id )
        {
            case 'balance_general':
                $gran_total = abs( $totales_clases[ 1 ] ) - abs( $totales_clases[ 2 ] ) - abs( $totales_clases[ 3 ] );
                break;
            
            default:
            $gran_total = abs( $totales_clases[ 4 ] ) - abs( $totales_clases[ 5 ] ) - abs( $totales_clases[ 6 ] );
                break;
        }        

        return View::make('contabilidad.reportes.tabla_eeff', compact('filas', 'anio', 'gran_total') )->render();
    }

    
    public function taxes_general_report( Request $request )
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $reports_list = [
            (object)[
                'title' => 'Impuestos en ventas (generados)',
                'arr_transactions_types' => [23,44,47,49,50,52],
                'campo_filtrar_ctas' => Impuesto::groupBy( 'cta_ventas_id' )
                                            ->get()
                                            ->pluck('cta_ventas_id')
                                            ->toArray()
            ],
            (object)[
                'title' => 'Impuestos por devoluciones en ventas',
                'arr_transactions_types' => [38,41,53,54],
                'campo_filtrar_ctas' => Impuesto::groupBy( 'cta_ventas_devol_id' )
                                            ->get()
                                            ->pluck('cta_ventas_devol_id')
                                            ->toArray()
            ],
            (object)[
                'title' => 'Impuestos en compras (descontables)',
                'arr_transactions_types' => [25,29,48],
                'campo_filtrar_ctas' => Impuesto::groupBy( 'cta_compras_id' )
                                            ->get()
                                            ->pluck('cta_compras_id')
                                            ->toArray()
            ],
            (object)[
                'title' => 'Impuestos por devoluciones en compras',
                'arr_transactions_types' => [36,40],
                'campo_filtrar_ctas' => Impuesto::groupBy( 'cta_compras_devol_id' )
                                            ->get()
                                            ->pluck('cta_compras_devol_id')
                                            ->toArray()
            ]
       ];

       $vista = '<table><tr><td>';
       foreach ($reports_list as $report) {
            // Obtener movimiento contable por cada grupo de transacciones
            $movements = ContabMovimiento::whereIn('core_tipo_transaccion_id',$report->arr_transactions_types)
                    ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                    ->whereIn('contab_cuenta_id',$report->campo_filtrar_ctas)
                    ->select( 
                            DB::raw( 'CAST(tasa_impuesto AS CHAR) as tasa_impuesto','' ),
                            'valor_saldo'
                        )
                    ->get();

            $group_taxes = $this->get_totals_by_tax($movements, 'tasa_impuesto', 'tasa_impuesto');
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
                'arr_transactions_types' => [23,44,47,49,50,52],
                'campo_filtrar_ctas' => Impuesto::groupBy( 'cta_ventas_id' )
                                            ->get()
                                            ->pluck('cta_ventas_id')
                                            ->toArray()
            ],
            'sales_return_taxes' => (object)[
                'title' => 'Impuestos por devoluciones en ventas',
                'arr_transactions_types' => [38,41,53,54],
                'campo_filtrar_ctas' => Impuesto::groupBy( 'cta_ventas_devol_id' )
                                            ->get()
                                            ->pluck('cta_ventas_devol_id')
                                            ->toArray()
            ],
            'purchases_taxes' => (object)[
                'title' => 'Impuestos en compras (descontables)',
                'arr_transactions_types' => [25,29,48],
                'campo_filtrar_ctas' => Impuesto::groupBy( 'cta_compras_id' )
                                            ->get()
                                            ->pluck('cta_compras_id')
                                            ->toArray()
            ],
            'purchases_return_taxes' => (object)[
                'title' => 'Impuestos por devoluciones en compras',
                'arr_transactions_types' => [36,40],
                'campo_filtrar_ctas' => Impuesto::groupBy( 'cta_compras_devol_id' )
                                            ->get()
                                            ->pluck('cta_compras_devol_id')
                                            ->toArray()
            ]
        ];

        $params = $reports_list[$request->tax_report_type];

        $vista = '<table><tr><td>';

        $arr_third_parties = ContabMovimiento::whereIn('core_tipo_transaccion_id',$params->arr_transactions_types)
                ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                ->whereIn('contab_cuenta_id',$params->campo_filtrar_ctas)
                ->groupBy( 'core_tercero_id' )
                ->get()
                ->pluck('core_tercero_id')
                ->toArray();

        $group_taxes_third_parties = [];
        foreach ($arr_third_parties as $key => $core_tercero_id) {

            $tercero = Tercero::find($core_tercero_id);

            $movements = ContabMovimiento::whereIn('core_tipo_transaccion_id',$params->arr_transactions_types)
                    ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                    ->whereIn('contab_cuenta_id',$params->campo_filtrar_ctas)
                    ->where('core_tercero_id',$core_tercero_id)
                    ->select( 
                            DB::raw( 'CAST(tasa_impuesto AS CHAR) as tasa_impuesto','' ),
                            'valor_saldo'
                        )
                    ->get();

            $group_taxes = $this->get_totals_by_tax($movements, 'tasa_impuesto', 'tasa_impuesto');
            
            foreach ($group_taxes as $key => $tax) {
                $group_taxes_third_parties[] = (object)[
                    'tercero_numero_identificacion' => $tercero->numero_identificacion,
                    'tercero_descripcion' => $tercero->descripcion,
                    'group' => $tax['group'],
                    'base_impuesto' => $tax['base_impuesto'],
                    'valor_impuesto' => $tax['valor_impuesto']
                ];
            }
        }

        $title = $params->title;

        $vista .= View::make( 'contabilidad.incluir.listado_iva_por_terceros', compact('group_taxes_third_parties','title') )->render();

        $vista .='</td></tr></table>';

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    public function get_totals_by_tax($contab_movements_lines, $column_to_group, $column_method_name)
    {
        $grouped_data = $contab_movements_lines->groupBy($column_to_group)->map(function ($row) use ($column_method_name) {
            return  [
                'group' => $row->first()->$column_method_name,
                'valor_impuesto' => $row->sum('valor_saldo')
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
                'group' => 'IVA ' . $line['group'] . '%',
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

        $arr_ids_cuentas = ContabCuenta::where( 'contab_cuenta_clase_id', $clase_cuenta_id )
                                   ->get()->pluck('id')->all();

        $valor_saldo = ContabMovimiento::whereBetween( 'fecha', [ $lapso1_ini, $lapso1_fin ] )
                            ->whereIn('contab_cuenta_id', $arr_ids_cuentas )
                            ->sum('valor_saldo');

        $arr_ids_grupos_padres = ContabCuentaGrupo::where( [
                                        ['grupo_padre_id', '=', 0],
                                        ['contab_cuenta_clase_id', '=', $clase_cuenta_id]
                                    ] )
                                        ->get()
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

        $arr_ids_grupos_hijos = ContabCuentaGrupo::where( [
                                        ['grupo_padre_id', '=', $grupo_padre_id]
                                    ] )
                                        ->get()
                                        ->pluck('id')
                                        ->all();

        $arr_ids_cuentas = ContabCuenta::whereIn( 'contab_cuenta_grupo_id', $arr_ids_grupos_hijos )
                                   ->get()->pluck('id')->all();

        $valor_saldo = ContabMovimiento::whereBetween( 'fecha', [ $lapso1_ini, $lapso1_fin ] )
                            ->whereIn('contab_cuenta_id', $arr_ids_cuentas )
                            ->sum('valor_saldo');

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

        $arr_ids_cuentas_aux = ContabCuenta::where( 'contab_cuenta_grupo_id', $grupo_hijo_id )
                                   ->get()->pluck('id')->all();

        $valor_saldo = 0;
        $arr_ids_cuentas = [];
        foreach ($arr_ids_cuentas_aux as $cuenta_id) {
            $aux_valor_saldo = ContabMovimiento::whereBetween( 'fecha', [ $lapso1_ini, $lapso1_fin ] )
                            ->where('contab_cuenta_id', $cuenta_id )
                            ->sum('valor_saldo');
            if ($aux_valor_saldo != 0) {
                $valor_saldo += $aux_valor_saldo;
                $arr_ids_cuentas[] = $cuenta_id;
            }
        }        

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

        $valor_saldo = ContabMovimiento::whereBetween( 'fecha', [ $lapso1_ini, $lapso1_fin ] )
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
}