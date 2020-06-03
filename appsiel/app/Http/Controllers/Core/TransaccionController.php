<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

// Controllers
use App\Http\Controllers\Sistema\CrudController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;
use App\Http\Controllers\Contabilidad\ContabilidadController;

// Facades
use Auth;
use DB;
use Input;
use View;
use Form;
use Lava;

// Modelos del core
use App\Sistema\Aplicacion;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Sistema\Campo;
use Spatie\Permission\Models\Permission;
use App\Core\Empresa;
use App\Core\Tercero;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\MigaPan;

// Otros Modelos
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvBodega;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\InvMotivo;

use App\Contabilidad\ContabMovimiento;

class TransaccionController extends Controller
{
    protected $doc_encabezado;
    protected $empresa, $app, $modelo, $transaccion, $variables_url;

    public function __construct()
    {
        $this->middleware('auth');
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

        $permisos = Permission::where( 'core_app_id', $this->app->id)
                                ->where('parent',0)
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
        // Botón crear con el listado de las transacciones asociadas a la aplicación
        $tipos_transacciones = $app->tipos_transacciones()->where('estado','Activo')->get();
        
        
        //dd($tipos_transacciones);
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
    public function crear( $app, $modelo, $transaccion, $vista, $tabla = null )
    {   
        if ( is_null($tabla) )
        {
            $tabla = '';
        }
        
        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($transaccion->id, $transaccion, $lista_campos, $cantidad_campos, 'create', null);

        $url_form_create = 'web';
        if ( $this->modelo->url_form_create != '')
        {
            $url_form_create = $this->modelo->url_form_create;
        }
        
        $form_create = [
                        'url' => $url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_array_miga_pan( $app, $modelo, 'Crear: '.$transaccion->descripcion );
        
        return view( $vista, compact('form_create','miga_pan','tabla'));
    }

    
    /*
        Crea el encabezado de un documento
        Devuelve LA INSTANCIA del documento creado
    */
    public function crear_encabezado_documento(Request $request, $modelo_id)
    {
        $request['creado_por'] = Auth::user()->email;
        return CrudController::crear_nuevo_registro( $request, $modelo_id );
    }


    // FOMRULARIO PARA EDITAR UN REGISTRO
    public function edit($id)
    {
        //
    }

    //     A L M A C E N A R  LA MODIFICACION DE UN REGISTRO
    public function update(Request $request, $id)
    {
        //
    }


    // Mostra el documento de una transacción
    public function show($id)
    {

        /**/
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
        // WARNING: EL COSTO SE DEBE CALCULAR TENIENDO EN CUENTA LA FECHA DE INGRESO DE LA TRANSACCION, SOLO SE DEBN TENER EN CUENTA LA SUMATORIA DESDE LA FECHA DE LA TRANSACCIÓN HACIA ATRÁS
        $costo_prom = InvMovimiento::where('inv_movimientos.inv_bodega_id','=',$id_bodega)
                                ->where('inv_movimientos.inv_producto_id','=',$id_producto)
                                ->where('inv_movimientos.fecha', '<=', $fecha_transaccion)
                                ->select(DB::raw('(sum(inv_movimientos.costo_total)/sum(inv_movimientos.cantidad)) AS Costo'))
                                ->get()
                                ->toArray();

        if ($costo_prom[0]['Costo']==0) {
            $costo_prom = $valor_default;
        }else{
            $costo_prom = $costo_prom[0]['Costo'];
        } 

        return $costo_prom;
    }

    public static function set_costo_promedio($id_bodega,$id_producto,$costo_prom)
    {
        $existe = InvCostoPromProducto::where('inv_bodega_id',$id_bodega)
                                ->where('inv_producto_id',$id_producto)
                                ->value('id');                  
        if( !is_null($existe))
        {
            // Si ya existe el costo promedio para ese producto en esa bodega, se actualiza
            InvCostoPromProducto::where('inv_bodega_id',$id_bodega)
                        ->where('inv_producto_id',$id_producto)
                        ->update(['costo_promedio' => $costo_prom]);
        }else{
            // Armo el vector de almacenamiento
            $datos = ['inv_bodega_id'=>$id_bodega,'inv_producto_id'=>$id_producto,'costo_promedio'=>$costo_prom];
                         
            // Almaceno en la base de datos
            InvCostoPromProducto::create($datos);
        }
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
}