<?php

namespace App\Http\Controllers\Core;

use App\Tesoreria\TesoMotivo;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

// Controllers
use App\Http\Controllers\Sistema\ModeloController;

// Facades
use Exception;

// Modelos del core
use App\Sistema\Aplicacion;
use App\Sistema\TipoTransaccion;
use App\Sistema\Modelo;
use Spatie\Permission\Models\Permission;
use App\Core\Empresa;
use App\Core\EncabezadoDocumentoTransaccion;

// Objetos
use App\Sistema\Html\MigaPan;

// Otros Modelos
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvCostoPromProducto;

use App\Contabilidad\ContabMovimiento;

use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;

use App\Core\Transactions\TransactionDocument;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class TransaccionController extends Controller
{
    protected $doc_encabezado;
    protected $empresa, $app, $modelo, $transaccion, $variables_url;

    protected $datos;

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function set_variables_globales()
    {
        $this->empresa = Empresa::find( Auth::user()->empresa_id );
        $this->app = Aplicacion::find( Input::get('id') );
        $this->modelo = Modelo::find( Input::get('id_modelo') );
        $this->transaccion = TipoTransaccion::find( Input::get('id_transaccion') );

        $this->variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
    }

    public function index()
    {
        $this->set_variables_globales();

        $select_crear = $this->get_boton_select_crear( $this->app );

        $miga_pan = [
                [ 'url' => 'NO', 'etiqueta' => $this->app->descripcion ]
            ];

        return view( $this->app->app.'.index', compact('miga_pan','select_crear') );
    }

    
    // Vista de los permisos asoaciados a la App
    // Como son demasiados permisos NO se muestran en un menú
    public function catalogos()
    {
        $this->set_variables_globales();

        $permisos = Permission::where( [
                                        ['core_app_id', '=', $this->app->id],
                                        ['parent','=',0],
                                        ['modelo_id','<>',0]
                                    ] )
                                ->orderBy('orden','ASC')
                                ->get()
                                ->toArray();

        $miga_pan = [
                        ['url' => $this->app->app.'?id='.$this->app->id, 'etiqueta' => $this->app->descripcion],
                        ['url' => 'NO', 'etiqueta' => 'Catálogos']
                    ];

        return view( $this->app->app.'.catalogos', compact('permisos', 'miga_pan') );
    }


    public function get_boton_select_crear( $app )
    {
        $tipos_transacciones = [];

        if ( !is_null( $app ) )
        {
            // Botón crear con el listado de las transacciones asociadas a la aplicación
            $tipos_transacciones = $app->tipos_transacciones()->where('estado','Activo')->orderBy('orden')->get();
        }
        
        $opciones = [];
        $key = 0;

        foreach($tipos_transacciones as $fila)
        {
            $modelo = Modelo::find( $fila->core_modelo_id );

            $variables_url = '?id='.Input::get('id').'&id_modelo='.$modelo->id.'&id_transaccion='.$fila->id;
            $acciones = $this->acciones_basicas_modelo( $modelo, $variables_url );

            $opciones[$key]['link'] = url( $acciones->create );
            $opciones[$key]['etiqueta'] = $fila->descripcion;
            $key++;
        }

        return FormFacade::bsBtnDropdown( 'Crear', 'primary', 'plus', $opciones );
    }

    public function get_array_miga_pan( $app, $modelo_crud, $etiqueta_final )
    {
        return MigaPan::get_array( $app, $modelo_crud, $etiqueta_final );
    }

    public function acciones_basicas_modelo( $modelo, $variables_url )
    {
        $model = new ModeloController();
        return $model->acciones_basicas_modelo( $modelo, $variables_url );
    }

    // FORMULARIO PARA CREAR UN NUEVO REGISTRO
    public function crear( $app, $modelo, $transaccion, $vista, $tabla = null, $item_sugerencia_cliente = null )
    {   
        if ( is_null($tabla) )
        {
            $tabla = '';
        }

        if ( is_null($tabla) )
        {
            $item_sugerencia_cliente = '';
        }
        
        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($transaccion->id, $transaccion, $lista_campos, $cantidad_campos, 'create', null);

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo( $modelo, '' );
        
        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $id_transaccion = 8;// 8 = Recaudo cartera

        $msj_resolucion_facturacion = '';
        switch ( $transaccion->id )
        {
            case 25: // Factura compras
                $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion( 'Pago proveedores' );
                break;
            case 48: // Doc. Soporte en adquisiciones no obligados a facturas (Compras)
                $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion( 'Pago proveedores' );
                break;
            case 23: // Factura ventas
                $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion( 'Recaudo cartera' );
                break;
            
            default:
                $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion( 'Recaudo cartera' );
                break;
        }
        
        $medios_recaudo = TesoMedioRecaudo::opciones_campo_select();
        $cajas = TesoCaja::opciones_campo_select();
        $cuentas_bancarias = TesoCuentaBancaria::opciones_campo_select();

        $miga_pan = $this->get_array_miga_pan( $app, $modelo, 'Crear: '.$transaccion->descripcion );
        
        return view( $vista, compact( 'form_create','miga_pan','tabla','id_transaccion','motivos','medios_recaudo','cajas','cuentas_bancarias', 'item_sugerencia_cliente', 'msj_resolucion_facturacion' ) );
    }
    
    /*
        Crea el encabezado de un documento
        Devuelve LA INSTANCIA del documento creado
    */
    public function crear_encabezado_documento(Request $request, $modelo_id)
    {
        if ( !isset($request['creado_por']) ) {
            $request['creado_por'] = Auth::user()->email;
        }

        $encabezado_documento = new EncabezadoDocumentoTransaccion( $modelo_id );

        return $encabezado_documento->crear_nuevo( $request->all() );
    }

    public static function get_registros_contabilidad( $doc_encabezado )
    {
        $registros_contabilidad = ContabMovimiento::get_registros_contables( $doc_encabezado->core_tipo_transaccion_id, $doc_encabezado->core_tipo_doc_app_id, $doc_encabezado->consecutivo );

        // Se eliminan las cuentas repetidas
        $cant = count($registros_contabilidad);
        for($i=0;$i<$cant ;$i++)
        {
            
            if ( isset( $registros_contabilidad[$i] ) )
            {
                for($j=$i+1; $j<$cant ;$j++)
                {
                    if ( $registros_contabilidad[$i]['cuenta_codigo'] == $registros_contabilidad[$j]['cuenta_codigo'] )
                    {
                        unset( $registros_contabilidad[$i] );
                        unset( $registros_contabilidad[$j] );
                        $cant -= 2;
                    }
                }
            }
                
        }/**/
            
        return $registros_contabilidad;
    }


    //
    // CALCULAR EL COSTO PROMEDIO
    public static function calcular_costo_promedio_old_OK($id_bodega,$id_producto,$valor_default, $fecha_transaccion, $cantidad)
    {

        // NOTA: Ya el registro del item está agregado en el movimiento

        $array_wheres = [
            ['inv_movimientos.inv_producto_id','=',$id_producto],
            ['inv_movimientos.fecha', '<=', $fecha_transaccion]
        ];

        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1 ) {
            $array_wheres = array_merge( $array_wheres, [ ['inv_movimientos.inv_bodega_id','=',$id_bodega] ] );
        }

        // COSTO PROMEDIO PONDERADO

        // NOTA: EL COSTO SE CALCULA TENIENDO EN CUENTA LA FECHA DE INGRESO DE LA TRANSACCION, SOLO SE TIENE EN CUENTA LA SUMATORIA DESDE LA FECHA DE LA TRANSACCIÓN HACIA ATRÁS
        $costo_prom = InvMovimiento::where( $array_wheres )
                                ->select(DB::raw('(sum(inv_movimientos.costo_total)/sum(inv_movimientos.cantidad)) AS Costo'))
                                ->get()
                                ->toArray();

        $cant = InvMovimiento::where( $array_wheres )
                            ->select(DB::raw('sum(inv_movimientos.cantidad) AS cantidad_total'))
                            ->get()
                            ->toArray();

        if ($cant[0]['cantidad_total'] <= 0 ) {
            $costo_prom = $valor_default;
        }else{
            $costo_prom = $costo_prom[0]['Costo'];
        }
         
        return $costo_prom;
    }

    public static function calcular_costo_promedio($id_bodega,$id_producto,$valor_default, $fecha_transaccion, $cantidad)
    {
        $array_wheres = [
            ['inv_producto_id','=',$id_producto],
            ['fecha', '<=', $fecha_transaccion]
        ];
        
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1 ) {
            $array_wheres = array_merge($array_wheres, [['inv_bodega_id','=',$id_bodega]]);
        }else{
            $bodega_id = 0;
        }
        
        // Obtener todas las cantidades del movimiento
        $cantidad_total_movim = InvMovimiento::where($array_wheres)->sum('cantidad');
        
        // Restar las cantidades de entrada
        $cantidad_total_movim_anterior_a_la_entrada = $cantidad_total_movim - $cantidad;
        
        if (round($cantidad_total_movim_anterior_a_la_entrada,0) <= 0) {
            return $valor_default;
        }
        
        $costo_total_entrada = $cantidad * $valor_default;
        
        // Validar si con las entradas quedan las cantidades en cero
        if (round($cantidad_total_movim,0) <= 0) {
            return $valor_default;
        }

        $item = InvProducto::find($id_producto);
        $costo_promedio_actual = $item->get_costo_promedio( $bodega_id );
        $costo_total_movim_anterior = $cantidad_total_movim_anterior_a_la_entrada * $costo_promedio_actual;

        return ($costo_total_movim_anterior + $costo_total_entrada) / $cantidad_total_movim;
    }

    // Almacenar el costo promedio en la tabla de la BD
    public static function set_costo_promedio($id_bodega,$id_producto,$costo_prom)
    {
        $item = InvProducto::find( $id_producto );
        $item->set_costo_promedio( $id_bodega, $costo_prom );
    }

    public function contabilizar_registro($contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0)
    {
        ContabMovimiento::create( $this->datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id]
                        );
    }

    public function get_total_campo_lineas_registros( $lineas_registros, string $campo )
    {
        $total = 0;

        foreach ($lineas_registros as $linea )
        {
            if ( isset($linea->$campo) )
            {
                $total += (float)$linea->$campo;
            }
        }
        
        return $total;
    }

    public function show($transaction_name)
    {
        //
    }

    public function store(Request $request)
    {
        if ( !isset($request->transaction_name)) {
            throw new Exception('Campo transaction_name NO enviado.');
        }
        $data = $request->all();
        //$transaction_name = ; 32// Recaudos de CxC. Por ahora se maneja el ID
        $transaction_doc = new TransactionDocument($request->transaction_name,$data);
        $transaction_doc->create( $data );
    }
}