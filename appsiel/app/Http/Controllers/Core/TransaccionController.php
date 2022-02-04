<?php

namespace App\Http\Controllers\Core;

use App\Tesoreria\TesoMotivo;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

// Controllers
use App\Http\Controllers\Sistema\ModeloController;

// Facades
use Auth;
use DB;
use Input;
use Form;
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

class TransaccionController extends Controller
{
    protected $doc_encabezado;
    protected $empresa, $app, $modelo, $transaccion, $variables_url;

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

        return Form::bsBtnDropdown( 'Crear', 'primary', 'plus', $opciones );
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
        
        return view( $vista, compact( 'form_create','miga_pan','tabla','id_transaccion','motivos','medios_recaudo','cajas','cuentas_bancarias', 'item_sugerencia_cliente' ) );
    }
    
    /*
        Crea el encabezado de un documento
        Devuelve LA INSTANCIA del documento creado
    */
    public function crear_encabezado_documento(Request $request, $modelo_id)
    {
        $request['creado_por'] = Auth::user()->email;

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
    public static function calcular_costo_promedio($id_bodega,$id_producto,$valor_default, $fecha_transaccion)
    {

        // NOTA: Ya el registro del item está agregado en el movimiento

        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1 ) {
            
            // COSTO PROMEDIO PONDERADO

            // NOTA: EL COSTO SE CALCULA TENIENDO EN CUENTA LA FECHA DE INGRESO DE LA TRANSACCION, SOLO SE TIENE EN CUENTA LA SUMATORIA DESDE LA FECHA DE LA TRANSACCIÓN HACIA ATRÁS
            $costo_prom = InvMovimiento::where('inv_movimientos.inv_bodega_id','=',$id_bodega)
                    ->where('inv_movimientos.inv_producto_id','=',$id_producto)
                    ->where('inv_movimientos.fecha', '<=', $fecha_transaccion)
                    ->select(DB::raw('(sum(inv_movimientos.costo_total)/sum(inv_movimientos.cantidad)) AS Costo'))
                    ->get()
                    ->toArray();

        }else{
            
            $costo_prom = InvMovimiento::where('inv_movimientos.inv_producto_id','=',$id_producto)
                    ->where('inv_movimientos.fecha', '<=', $fecha_transaccion)
                    ->select(DB::raw('(sum(inv_movimientos.costo_total)/sum(inv_movimientos.cantidad)) AS Costo'))
                    ->get()
                    ->toArray();
        }

        if ($costo_prom[0]['Costo']==0) {
            $costo_prom = $valor_default;
        }else{
            $costo_prom = $costo_prom[0]['Costo'];
        }
         
        return $costo_prom;
    }

    // Almacenar el costo promedio en la tabla de la BD
    public static function set_costo_promedio($id_bodega,$id_producto,$costo_prom)
    {
        
        $item = InvProducto::find( $id_producto );
        $item->set_costo_promedio( $id_bodega, $costo_prom );

        /*
        $costo_prom = round( $costo_prom, 2 );
        
        $existe = InvCostoPromProducto::where('inv_bodega_id',$id_bodega)
                                ->where('inv_producto_id',$id_producto)
                                ->value('id');                  
        if( !is_null($existe))
        {
            // Si ya existe el costo promedio para ese producto en esa bodega, se actualiza
            InvCostoPromProducto::where('inv_bodega_id',$id_bodega)
                        ->where('inv_producto_id',$id_producto)
                        ->update(['costo_promedio' => abs( $costo_prom ) ]);
        }else{
            // Armo el vector de almacenamiento
            $datos = [
                        'inv_bodega_id'=>$id_bodega,
                        'inv_producto_id'=>$id_producto,
                        'costo_promedio'=> abs( $costo_prom )
                    ];
                         
            // Almaceno en la base de datos
            InvCostoPromProducto::create( $datos );
        }

        // Se actualiza el costo estándar del Item
        $item = InvProducto::find( $id_producto );
        $item->precio_compra = abs( $costo_prom );
        $item->save();
        */
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