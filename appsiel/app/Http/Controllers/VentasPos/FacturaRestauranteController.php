<?php

namespace App\Http\Controllers\VentasPos;

use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Tesoreria\RecaudoController;
use App\Inventarios\InvProducto;
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Services\ModeloService;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Ventas\ResolucionFacturacion;
use App\Ventas\Vendedor;
use App\VentasPos\Pdv;
use App\VentasPos\PreparaTransaccion;
use App\VentasPos\Services\FacturaPosService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class FacturaRestauranteController extends TransaccionController
{    
    public function create()
    {
        $pdv = Pdv::find(Input::get('pdv_id'));
        $factura_pos_service = new FacturaPosService();

        $validar = $factura_pos_service->verificar_datos_por_defecto( $pdv );
        if ( $validar != 'ok' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $validar );
        }

        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida' => 'Ventas POS'];

        $inv_motivo_id = 10;

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(PreparaTransaccion::get_datos_tabla_ingreso_lineas_registros($this->transaccion, $motivos));

        if (is_null($tabla)) {
            $tabla = '';
        }

        $user = Auth::user();


        /**
         * Validar resolución de Facturación
         */
        $msj_resolucion_facturacion = $factura_pos_service->get_msj_resolucion_facturacion( $pdv );        
        if ( $msj_resolucion_facturacion->status == 'error' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $msj_resolucion_facturacion->message );
        }
        $msj_resolucion_facturacion = $msj_resolucion_facturacion->message;

        /**
         * Asignar campos por defecto
         */
        $cliente = $pdv->cliente;
        $vendedor = $cliente->vendedor;

        $modelo_service = new ModeloService();
        
        $lista_campos = $modelo_service->get_campos_modelo($this->modelo, '', 'create');

        $lista_campos = $factura_pos_service->ajustar_campos( $lista_campos, $pdv, $vendedor, $this->transaccion);

        $fecha = date('Y-m-d');
        if(config('ventas_pos.asignar_fecha_apertura_a_facturas'))
        {
            $fecha = $pdv->ultima_fecha_apertura();
        }
        $fecha_vencimiento = $pdv->cliente->fecha_vencimiento_pago( $fecha );

        //$modelo_controller = new ModeloController;
        $acciones = $modelo_service->acciones_basicas_modelo($this->modelo, '');
        
        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $id_transaccion = 8; // 8 = Recaudo cartera
        $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion('Recaudo cartera');
        $medios_recaudo = RecaudoController::get_medios_recaudo();

        if ( $user->hasRole('Cajero PDV') || $user->hasRole('Cajero Junior') ) {
            $cajas =  [ $pdv->caja->id => $pdv->caja->descripcion ];
        }else{
            $cajas =  TesoCaja::opciones_campo_select();
        }
        
        $cuentas_bancarias = TesoCuentaBancaria::opciones_campo_select();

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Punto de ventas: ' . $pdv->descripcion);

        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        $productos = $productos->sortBy('precio_venta');
        
        $productosTemp = $factura_pos_service->get_productos($pdv,$productos);
        
        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.tags_lista_items', compact('productosTemp'))->render();
        }
        
        // Para visualizar el listado de productos
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $factura_pos_service->generar_plantilla_factura($pdv, $this->empresa);

        $pedido_id = 0;

        $lineas_registros = '<tbody></tbody>';

        $numero_linea = 1;

        $valor_subtotal = 0;
        $valor_descuento = 0;
        $valor_total_impuestos = 0;

        $valor_sub_total_factura = 0;
        $valor_lbl_propina = 0;
        $valor_lbl_datafono = 0;

        $valor_total_factura = 0;
        $total_efectivo_recibido = 0;

        $vendedores = Vendedor::where('estado','Activo')->get();

        $params_JSPrintManager = $factura_pos_service->get_parametros_complemento_JSPrintManager($pdv);

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();
        
        $pdv_descripcion = $pdv->descripcion;
        $tipo_doc_app = $pdv->tipo_doc_app;

        $medios_pago = null;

        $resolucion_facturacion_electronica = $factura_pos_service->get_resolucion_facturacion_electronica();

        return view('ventas_pos.crud_factura', compact('form_create', 'miga_pan', 'tabla', 'pdv', 'inv_motivo_id', 'contenido_modal', 'vista_categorias_productos', 'plantilla_factura', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias','cliente', 'pedido_id', 'lineas_registros', 'numero_linea','valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'total_efectivo_recibido', 'vendedores','vendedor','fecha','fecha_vencimiento', 'params_JSPrintManager','resolucion','msj_resolucion_facturacion', 'pdv_descripcion','tipo_doc_app', 'valor_sub_total_factura' , 'valor_lbl_propina', 'valor_lbl_datafono', 'medios_pago', 'resolucion_facturacion_electronica'));
    }

}