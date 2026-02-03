<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Tesoreria\RecaudoController;
use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Inventarios\InvProducto;

use App\VentasPos\PreparaTransaccion;

use App\Ventas\VtasDocRegistro AS DocRegistro;

use App\Ventas\VtasPedido;
use App\Ventas\Vendedor;

use App\VentasPos\Pdv;

use App\Ventas\Cliente;

use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;

use App\Tesoreria\TesoMotivo;

use App\Inventarios\InvGrupo;
use App\Ventas\Services\PedidosRestauranteServices;
use App\VentasPos\Services\CrudService;
use App\VentasPos\Services\RecipeServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class PedidoRestauranteController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    public function create()
    {
        $pdv_id = config('ventas_pos.pdv_id_default',1);
        $pdv = Pdv::find($pdv_id);        
        
        $validar = $this->verificar_datos_por_defecto( $pdv );
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
        
        $cliente = Cliente::find(config('pedidos_restaurante.cliente_default_id'));
        $vendedor = $cliente->vendedor;

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, $cantidad_campos, 'create', null);

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++)
        {
            switch ($lista_campos[$i]['name']) {

                case 'core_tipo_doc_app_id':
                    $opciones = [];                    
                    if (!is_null($this->transaccion)) {
                        $tipo_docs_app = $this->transaccion->tipos_documentos;
                        foreach ($tipo_docs_app as $fila) {
                            $opciones[$fila->id] = $fila->prefijo . " - " . $fila->descripcion;
                        }
                    }else{
                        $opciones = [$pdv->tipo_doc_app_default_id => $pdv->tipo_doc_app->prefijo . " - " . $pdv->tipo_doc_app->descripcion];
                    }
                    $lista_campos[$i]['opciones'] = $opciones;
                    break;

                case 'cliente_input':
                    $lista_campos[$i]['value'] = $pdv->cliente->tercero->descripcion;
                    break;

                case 'vendedor_id':
                    $lista_campos[$i]['value'] = [$vendedor->id];
                    break;

                case 'forma_pago':
                    $lista_campos[$i]['value'] = $pdv->cliente->forma_pago( date('Y-m-d') );
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$i]['value'] = $pdv->cliente->fecha_vencimiento_pago( $pdv->ultima_fecha_apertura() );
                    break;

                case 'fecha':
                    $fecha = date('Y-m-d');
                    if(config('ventas_pos.asignar_fecha_apertura_a_facturas'))
                    {
                        $fecha = $pdv->ultima_fecha_apertura();
                    }
                    $lista_campos[$i]['value'] = $fecha;
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$i]['opciones'] = [$pdv->bodega_default_id => $pdv->bodega->descripcion];
                    break;
                default:
                    # code...
                    break;
            }
        }

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo($this->modelo, '');
        
        $form_create = [
                            'url' => $acciones->store,
                            'campos' => $lista_campos
                        ];

        $id_transaccion = 8; // 8 = Recaudo cartera
        $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion('recaudo-cartera');
        $medios_recaudo = RecaudoController::get_medios_recaudo();
        $cajas = RecaudoController::get_cajas();
        $cuentas_bancarias = RecaudoController::get_cuentas_bancarias();
        
        $url_index = 'ventas?id=' . Input::get('id');
        
        $cocinas = config('pedidos_restaurante.cocinas');

        $miga_pan = [
            ['url' => 'ventas?id=' . Input::get('id'), 'etiqueta' => 'Ventas'],
            ['url' => $url_index, 'etiqueta' => 'Cocinas'],
            ['url' => 'NO', 'etiqueta' => $cocinas[Input::get('cocina_index')]['label']]
        ];

        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        
        $categoria_cocina = InvGrupo::find((int)Input::get('grupo_inventarios_id'));
        
        $productosTemp = null;
        foreach ($productos as $pr)
        {
            if ( $categoria_cocina != null) {
                if ( $pr->inv_grupo_id != $categoria_cocina->id) {
                    continue;
                }
            }
            
            $grupo_inventario = InvGrupo::find($pr->inv_grupo_id);

            if (!$grupo_inventario->mostrar_en_pagina_web) {
                continue;
            }

            if ( $grupo_inventario == null )
            {
                return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', 'El producto ' . $pr->descripcion . ' no tiene un grupo de inventario válido.' );
            }

            $pr->categoria = $grupo_inventario->descripcion;
            $productosTemp[$pr->categoria][] = $pr;
        }

        $vista_categorias_productos = View::make('ventas_pos.lista_items_pedidos_restaurantes', compact('productosTemp'))->render();
        
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $this->generar_plantilla_pedido($pdv);

        $pedido_id = 0;

        $lineas_registros = '<tbody></tbody>';

        $numero_linea = 1;

        $valor_subtotal = 0;
        $valor_descuento = 0;
        $valor_total_impuestos = 0;
        $valor_total_factura = 0;
        $total_efectivo_recibido = 0;

        $vendedores = Vendedor::where('estado','Activo')->get();
        
        $mesas = $this->get_mesas();

        return view('ventas.pedidos.restaurante.crud_pedido', compact('form_create', 'miga_pan', 'tabla', 'pdv', 'inv_motivo_id', 'contenido_modal', 'vista_categorias_productos', 'plantilla_factura', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias','cliente', 'pedido_id', 'lineas_registros', 'numero_linea','valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'total_efectivo_recibido', 'vendedores','vendedor','mesas'));
    }

    public function get_mesas()
    {
        return Cliente::where([
            ['estado','=','Activo'],
            ['clase_cliente_id','=',(int)config('pedidos_restaurante.clase_cliente_tipo_mesas_id')]
            ])
            ->orWhere('id',(int)config('pedidos_restaurante.cliente_default_id'))
            ->get();
    }

    public function verificar_datos_por_defecto( $pdv )
    {        
        if ( $pdv->estado != 'Abierto' ) {
            return $pdv->descripcion . ' debe estar "Abierto" para poder crear pedidos.';
        }

        $cliente = Cliente::find(config('pedidos_restaurante.cliente_default_id'));

        if ( $cliente == null ) {
            return 'En la configuración del restaurante NO hay asignado un Cliente por defecto.';
        }

        if ( $cliente->vendedor == null ) {
            return 'El Cliente por defecto NO tiene asociado un Vendedor.';
        }

        if ( $pdv->bodega == null ) {
            return 'El punto de ventas NO tiene asociada una Bodega por defecto.';
        }

        /*
        if ( $pdv->caja == null ) {
            return 'El punto de ventas NO tiene asociada una Caja por defecto.';
        }
        */

        if ( $pdv->cajero == null ) {
            return 'El punto de ventas NO tiene asociado un Cajero por defecto.';
        }

        if ( $pdv->tipo_doc_app == null ) {
            return 'El punto de ventas NO tiene asociado un Tipo de documento por defecto.';
        }

        return 'ok';
    }

    /**
     * ALMACENA FACTURA POS - ES LLAMADO VÍA AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lineas_registros = json_decode($request->lineas_registros);

        // Crear documento de Ventas
        $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);
        
        if ((int)config('inventarios.manejar_platillos_con_contorno')) {
            $lineas_registros = (new RecipeServices)->cambiar_items_con_contornos($lineas_registros);
        }

        if ($doc_encabezado->core_tercero_id == 0)
        {
            $pdv = Pdv::find($doc_encabezado->pdv_id);
            $doc_encabezado->core_tercero_id = $pdv->cliente->tercero->id;
            $doc_encabezado->save();
        }

        // Crear Registros del documento de ventas
        $request['creado_por'] = Auth::user()->email;
        self::crear_registros_documento($request, $doc_encabezado, $lineas_registros);

        //return $doc_encabezado->consecutivo;
        return response()->json( $this->build_json_pedido($doc_encabezado), 200);
    }

    public function edit($id)
    {
        $pedido_serv = new PedidosRestauranteServices();
        return response()->json( $pedido_serv->cargar_datos_editar_pedido($id) );
    }

    public function update(Request $request, $id)
    {
        $pedido = VtasPedido::find($id);

        if (!str_contains($pedido->descripcion,'<<Modificado>>')) {
            $pedido->descripcion .= ' <<Modificado>>';
            $pedido->save();
        }        

        $lineas_registros = $pedido->lineas_registros;
        foreach ($lineas_registros as $linea) {
            $linea->delete();
        }

        $lineas_registros = json_decode($request->lineas_registros);

        // Crear Registros del documento de ventas
        $request['creado_por'] = Auth::user()->email;
        self::crear_registros_documento($request, $pedido, $lineas_registros);

        return response()->json( $this->build_json_pedido($pedido), 200);
    }

    /**
     * ANULA PEDIDO - ES LLAMADO VÍA AJAX
     */
    public function cancel($id, $user_email)
    {
        $pedido = VtasPedido::find($id);

        $pedido->estado = 'Anulado';
        $pedido->modificado_por = $user_email;
        $pedido->save();

        $lineas_registros = $pedido->lineas_registros;
        foreach ($lineas_registros as $linea) {
            $linea->estado = 'Anulado';
            $linea->modificado_por = $user_email;
            $linea->save();
        }

        return response()->json( $this->build_json_pedido($pedido), 200);
    }

    public function build_json_pedido($doc_encabezado)
    {
        $hora_creacion = $doc_encabezado->created_at;

        return [
            'doc_encabezado_documento_transaccion_descripcion' => $doc_encabezado->tipo_documento_app->descripcion,
            'doc_encabezado_documento_transaccion_prefijo_consecutivo' => $doc_encabezado->tipo_documento_app->prefijo . ' ' . $doc_encabezado->consecutivo,
            'doc_encabezado_fecha' => $doc_encabezado->fecha,
            'doc_encabezado_hora_creacion' => $hora_creacion ? $hora_creacion->format('H:i a') : '',
            'doc_encabezado_tercero_nombre_completo' => $doc_encabezado->cliente->tercero->descripcion,
            'doc_encabezado_vendedor_descripcion' => $doc_encabezado->vendedor->tercero->descripcion,
            'cantidad_total_productos' => count($doc_encabezado->lineas_registros),
            'doc_encabezado_descripcion' => $doc_encabezado->descripcion
        ];
        
    }

    /*
        Crea los registros de un documento.
        No Devuelve nada.
    */
    public static function crear_registros_documento(Request $request, $doc_encabezado, array $lineas_registros)
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();
        
        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            if ( (int)$lineas_registros[$i]->inv_producto_id == 0)
            {
                continue; // Evitar guardar registros con productos NO validos
            }

            $linea_datos = ['vtas_motivo_id' => (int)$request->inv_motivo_id] +
                            ['inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id] +
                            ['precio_unitario' => (float)$lineas_registros[$i]->precio_unitario] +
                            ['cantidad' => (float)$lineas_registros[$i]->cantidad] +
                            ['precio_total' => (float)$lineas_registros[$i]->precio_total] +
                            ['base_impuesto' => (float)$lineas_registros[$i]->base_impuesto] +
                            ['tasa_impuesto' => (float)$lineas_registros[$i]->tasa_impuesto] +
                            ['valor_impuesto' => (float)$lineas_registros[$i]->valor_impuesto] +
                            ['base_impuesto_total' => (float)$lineas_registros[$i]->base_impuesto_total] +
                            ['tasa_descuento' => (float)$lineas_registros[$i]->tasa_descuento] +
                            ['valor_total_descuento' => (float)$lineas_registros[$i]->valor_total_descuento] +
                            ['creado_por' => Auth::user()->email] +
                            ['estado' => 'Pendiente'] +
                            ['vtas_doc_encabezado_id' => $doc_encabezado->id];

            $registro_creado = DocRegistro::create($linea_datos);

            $datos['consecutivo'] = $doc_encabezado->consecutivo;

            $total_documento += (float)$lineas_registros[$i]->precio_total;
        } // Fin por cada registro

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->updated_at = NULL;
        $doc_encabezado->timestamps = false;
        $doc_encabezado->save();
        $doc_encabezado->timestamps = true;

        return 0;
    }

    public function generar_plantilla_pedido($pdv)
    {
        $this->set_variables_globales();

        $empresa = $this->empresa;
        if ( $pdv->direccion != '' )
        {
            $empresa->direccion1 = $pdv->direccion;
            $empresa->telefono1 = $pdv->telefono;
            $empresa->email = $pdv->email;
        }

        $etiquetas = $this->get_etiquetas();

        return View::make('ventas.pedidos.formatos_impresion.pos_restaurante', compact('empresa', 'etiquetas', 'pdv'))->render();
    }

    public function get_etiquetas()
    {
        $parametros = config('ventas');

        $encabezado = '';

        if ($parametros['encabezado_linea_1'] != '') {
            $encabezado .= $parametros['encabezado_linea_1'];
        }

        if ($parametros['encabezado_linea_2'] != '') {
            $encabezado .= '<br>' . $parametros['encabezado_linea_2'];
        }

        if ($parametros['encabezado_linea_3'] != '') {
            $encabezado .= '<br>' . $parametros['encabezado_linea_3'];
        }


        $pie_pagina = '';

        if ($parametros['pie_pagina_linea_1'] != '') {
            $pie_pagina .= $parametros['pie_pagina_linea_1'];
        }

        if ($parametros['pie_pagina_linea_2'] != '') {
            $pie_pagina .= '<br>' . $parametros['pie_pagina_linea_2'];
        }

        if ($parametros['pie_pagina_linea_3'] != '') {
            $pie_pagina .= '<br>' . $parametros['pie_pagina_linea_3'];
        }

        return ['encabezado' => $encabezado, 'pie_pagina' => $pie_pagina];
    }

    public function set_catalogos( $pdv_id )
    {
        return (new CrudService())->set_catalogos( $pdv_id );
    }

    // mesero_id = vendedor_id
    public function get_mesas_disponibles_mesero($mesero_id)
    {
        $obj = new PedidosRestauranteServices();
        return response()->json( $obj->get_mesas_disponibles_mesero($mesero_id) );
    }

    public function get_pedidos_pendientes_mesero($mesero_id)
    {
        $obj = new PedidosRestauranteServices();
        return response()->json( $obj->get_pedidos_pendientes_mesero($mesero_id) );
    }

    public function get_pedidos_mesero_para_una_mesa($mesero_id, $mesa_id)
    {
        $obj = new PedidosRestauranteServices();
        return response()->json( $obj->get_pedidos_mesero_para_una_mesa($mesero_id, $mesa_id) );
    }

    public function cargar_datos_editar_pedido($pedido_id)
    {
        $obj = new PedidosRestauranteServices();
        return response()->json( $obj->cargar_datos_editar_pedido($pedido_id) );
    }

    public function cambiar_pedidos_de_mesa($mesa_pedidos_id, $nueva_mesa_id)
    {
        $obj = new PedidosRestauranteServices();
        return $obj->cambiar_pedidos_de_mesa($mesa_pedidos_id, $nueva_mesa_id);
    }

    public function mesas_permitidas_para_cambiar()
    {
        $obj = new PedidosRestauranteServices();
        return $obj->mesas_permitidas_para_cambiar();
    }

    public function pruebas()
    {
        $obj = new PedidosRestauranteServices();
        return response()->json( $obj->cargar_datos_editar_pedido(  308  ) );
    }

}
