<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;

use App\Inventarios\InvProducto;

use App\VentasPos\PreparaTransaccion;

use App\Ventas\VtasPedido;
use App\Ventas\Vendedor;

use App\VentasPos\Pdv;

use App\Ventas\Cliente;

use App\Ventas\ListaPrecioDetalle;

use App\Ventas\VtasDocEncabezado;
use App\Inventarios\InvGrupo;
use App\Sistema\TipoTransaccion;
use App\Ventas\Services\CustomerServices;
use App\Ventas\Services\DocumentsLinesServices;
use App\Ventas\VtasDocRegistro;

class PedidosPosController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida' => 'Ventas POS'];

        $inv_motivo_id = 10;
        $transaccion_factura_pos = TipoTransaccion::find( 47 );

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(PreparaTransaccion::get_datos_tabla_ingreso_lineas_registros($transaccion_factura_pos, $motivos));

        if (is_null($tabla)) {
            $tabla = '';
        }

        $user = Auth::user();

        $pdv = Pdv::find(Input::get('pdv_id'));

        $cliente = $pdv->cliente;
        $vendedor = $cliente->vendedor;
        
        $validar = $this->verificar_datos_por_defecto( $pdv );
        if ( $validar != 'ok' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $validar );
        }
        
        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');

        $lista_campos = $this->ajustar_campos($lista_campos,$pdv,$vendedor);

        $fecha = date('Y-m-d');
        if(config('ventas_pos.asignar_fecha_apertura_a_facturas'))
        {
            $fecha = $pdv->ultima_fecha_apertura();
        }
        $fecha_vencimiento = $pdv->cliente->fecha_vencimiento_pago( $fecha );
        
        $form_create = [
                            'url' => 'pos_pedido',
                            'campos' => $lista_campos
                        ];

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Punto de ventas: ' . $pdv->descripcion);

        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        
        $productosTemp = $this->get_productos($pdv,$productos);
        
        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.tags_lista_items', compact('productosTemp'))->render();
        }
        
        // Para visualizar el listado de productos
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_pedido = $this->generar_plantilla_pedido($pdv);

        $pedido_id = 0;

        $lineas_registros = '<tbody></tbody>';

        $numero_linea = 1;

        $valor_subtotal = 0;
        $valor_descuento = 0;
        $valor_total_impuestos = 0;
        $valor_total_factura = 0;
        $total_efectivo_recibido = 0;

        $vendedores = Vendedor::where('estado','Activo')->get();
        
        $pdv_descripcion = $pdv->descripcion;

        $tipo_doc_app = $this->transaccion->tipos_documentos->first();

        $msj_resolucion_facturacion = '';

        return view('ventas_pos.crud_pedido_pos', compact('form_create', 'miga_pan', 'tabla', 'pdv', 'inv_motivo_id', 'contenido_modal', 'vista_categorias_productos', 'plantilla_pedido','cliente', 'pedido_id', 'lineas_registros', 'numero_linea','valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'total_efectivo_recibido', 'vendedores','vendedor','fecha','fecha_vencimiento', 'pdv_descripcion','tipo_doc_app','msj_resolucion_facturacion'));
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
        $doc_encabezado = $this->crear_encabezado_documento($request, $request->url_id_modelo);

        if ($doc_encabezado->core_tercero_id == 0)
        {
            $pdv = Pdv::find($doc_encabezado->pdv_id);
            $doc_encabezado->core_tercero_id = $pdv->cliente->tercero->id;
            $doc_encabezado->save();
        }

        // Crear Registros del documento de ventas
        $request['creado_por'] = Auth::user()->email;

        $lineas_registros = json_decode($request->lineas_registros);
        $request['estado'] = "Pendiente";

        if (!isset($request['vendedor_id'])) {
            
            $request['vendedor_id'] = config('ventas.vendedor_id');

            $cliente = Cliente::find($request['cliente_id']);
            if ($cliente != null) {
                $request['vendedor_id'] = $cliente->vendedor_id;
            }
        }

        (new DocumentsLinesServices())->crear_registros_documento($request, $doc_encabezado, $lineas_registros);

        return $doc_encabezado->consecutivo;
    }

    /**
     * Prepara la vista para Editar una Factura POS
     */
    public function edit($id)
    {
        $this->set_variables_globales();

        // Se obtiene el registro a modificar del modelo
        $registro = app($this->modelo->name_space)->find($id); // Encabezado FActura POS

        $pdv = Pdv::find( Input::get('pdv_id') );

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, $registro, 'edit');

        $doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);

        $cantidad = count($lista_campos);

        $eid = '';

		if( config("configuracion.tipo_identificador") == 'NIT') { 
            $eid = number_format( $doc_encabezado->numero_identificacion, 0, ',', '.');
        }else { 
            $eid = $doc_encabezado->numero_identificacion;
        }

        // Agregar al comienzo del documento
        array_unshift($lista_campos, [
            "id" => 201,
            "descripcion" => "Empresa",
            "tipo" => "personalizado",
            "name" => "encabezado",
            "opciones" => "",
            "value" => '<div style="border: solid 1px #ddd; padding-top: -20px;">
                                                            <b style="font-size: 1.6em; text-align: center; display: block;">
                                                                ' . $doc_encabezado->documento_transaccion_descripcion . '
                                                                <br/>
                                                                <b>No.</b> ' . $doc_encabezado->documento_transaccion_prefijo_consecutivo . '
                                                                <br/>
                                                                <b>Fecha:</b> ' . $doc_encabezado->fecha . '
                                                            </b>
                                                            <br/>
                                                            <b>Cliente:</b> ' . $doc_encabezado->tercero_nombre_completo . '
                                                            <br/>
                                                            <b>'.config("configuracion.tipo_identificador").' &nbsp;&nbsp;</b> ' . $eid. '
                                                        </div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        //Personalización de la lista de campos
        foreach ($lista_campos as $key => $value)
        {
            switch ($value['name'])
            {
                case 'cliente_input':
                    $lista_campos[$key]['value'] = $doc_encabezado->tercero_nombre_completo;
                    break;

                case 'vendedor_id':
                    $lista_campos[$key]['value'] = [$doc_encabezado->vendedor_id];
                    break;

                case 'core_tipo_doc_app_id':
                    $lista_campos[$key]['editable'] = 1;
                    $lista_campos[$key]['atributos'] = [];
                    $lbl_value = $lista_campos[$key]['opciones'][$lista_campos[$key]['value']];
                    $lista_campos[$key]['opciones'] = [
                        $lista_campos[$key]['value'] => $lbl_value
                    ];
                    break;

                case 'forma_pago':
                    $lista_campos[$key]['value'] = $doc_encabezado->condicion_pago;
                    $lista_campos[$key]['editable'] = 1;
                    $lista_campos[$key]['atributos'] = [];
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$key]['value'] = $doc_encabezado->fecha_vencimiento;
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$key]['opciones'] = [$pdv->bodega_default_id => $pdv->bodega->descripcion];
                    break;
                default:
                    # code...
                    break;
            }
        }

        $fecha = $doc_encabezado->fecha;
        $fecha_vencimiento = $doc_encabezado->fecha_vencimiento;

        $url_action = 'pos_pedido/' . $registro->id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion');

        $form_create = [
            'url' => 'pos_pedido/' . $registro->id,
            'campos' => $lista_campos
        ];

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Punto de ventas: ' . $pdv->descripcion . '.' . ' Modificar: ' . $doc_encabezado->documento_transaccion_prefijo_consecutivo);

        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $motivos = ['10-salida' => 'Ventas POS'];
        $inv_motivo_id  = 10;
        $transaccion_factura_pos = TipoTransaccion::find( 47 );

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(PreparaTransaccion::get_datos_tabla_ingreso_lineas_registros($transaccion_factura_pos, $motivos));

        if (is_null($tabla)) {
            $tabla = '';
        }

        $numero_linea = count($registro->lineas_registros) + 1;

        $lineas_registros = $this->armar_cuerpo_tabla_lineas_registros($registro->lineas_registros);
        
        $total_efectivo_recibido = '11111';
        
        //$total_efectivo_recibido = 0;
        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        $productosTemp = $this->get_productos($pdv,$productos);

        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.tags_lista_items', compact('productosTemp'))->render();
        }
        
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_pedido = $this->generar_plantilla_pedido($pdv);

        $redondear_centena = config('ventas_pos.redondear_centena');
        
        $cliente = $registro->cliente;
        $vendedor = $registro->vendedor;

        $pedido_id = 0;

        $valor_subtotal = number_format($registro->lineas_registros->sum('base_impuesto_total') + $registro->lineas_registros->sum('valor_total_descuento'),'2',',','.');

        $valor_descuento = number_format( $registro->lineas_registros->sum('valor_total_descuento'),'2',',','.');

        $valor_total_impuestos = number_format( $registro->lineas_registros->sum('precio_total') - $registro->lineas_registros->sum('base_impuesto_total'),'2',',','.');

        $valor_total_factura = $registro->lineas_registros->sum('precio_total');

        $vendedores = Vendedor::where('estado','Activo')->get();

        $msj_resolucion_facturacion = '';

        return view('ventas_pos.crud_pedido_pos', compact('form_create', 'miga_pan', 'registro', 'archivo_js', 'url_action', 'pdv', 'inv_motivo_id', 'tabla', 'productos', 'contenido_modal', 'plantilla_pedido', 'redondear_centena', 'numero_linea', 'lineas_registros', 'total_efectivo_recibido','vista_categorias_productos','cliente', 'pedido_id', 'valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'vendedores','vendedor','fecha','fecha_vencimiento','msj_resolucion_facturacion'));
    }

    /**
     *
     */
    public function update(Request $request, $id)
    {
        $lineas_registros = json_decode($request->lineas_registros);
        $total_factura = $this->get_total_factura_from_arr_lineas_registros($lineas_registros);

        $doc_encabezado = VtasDocEncabezado::find($id);
        $doc_encabezado->fecha = $request->fecha;
        $doc_encabezado->descripcion = $request->descripcion;
        $doc_encabezado->forma_pago = $request->forma_pago;
        $doc_encabezado->fecha_vencimiento = $request->fecha_vencimiento;
        $doc_encabezado->vendedor_id = $request->vendedor_id;
        $doc_encabezado->valor_total = $total_factura;
        $doc_encabezado->modificado_por = Auth::user()->email;
        $doc_encabezado->save();

        // Borrar líneas de registros anteriores
        VtasDocRegistro::where('vtas_doc_encabezado_id', $doc_encabezado->id)->delete();

        // Crear nuevamente las líneas de registros
        $request['creado_por'] = Auth::user()->email;
        $request['modificado_por'] = Auth::user()->email;

        (new DocumentsLinesServices())->crear_registros_documento($request, $doc_encabezado, $lineas_registros);

        return $doc_encabezado->consecutivo;
    }

    public static function actualizar_cantidades_pendientes( $encabezado_pedido, $operacion )
    {
        $lineas_registros_pedido = $encabezado_pedido->lineas_registros;
        foreach( $lineas_registros_pedido AS $linea_pedido )
        {            
            if ( $operacion == 'restar' )
            {
                $linea_pedido->cantidad_pendiente = 0;
            }else{
                // sumar: al anular
                $linea_pedido->cantidad_pendiente = $linea_pedido->cantidad;
            }
                
            $linea_pedido->save();
        }
    }

    public function unificar_lineas_registros_pedidos($pedido)
    {
        $todos_los_pedidos = $this->get_todos_los_pedidos_mesero_para_la_mesa($pedido);
        
        $todas_las_lineas_registros = [];
        foreach ($todos_los_pedidos as $pedido) {
            $lineas_registros = $pedido->lineas_registros;
            foreach ($lineas_registros as $linea) {
                $todas_las_lineas_registros[] = $linea;
            }
        }

        return $todas_las_lineas_registros;
    }

    public function armar_cuerpo_tabla_lineas_registros($lineas_registros_documento)
    {
        $user = Auth::user();

        $cuerpo_tabla_lineas_registros = '<tbody>';
        $i = 1;
        foreach ($lineas_registros_documento as $linea) {

            if ($linea->item == null ) {
                continue;
            }

            $cuerpo_tabla_lineas_registros .= '<tr class="linea_registro" data-numero_linea="' . $i . '"><td style="display: none;"><div class="inv_producto_id">' . $linea->inv_producto_id . '</div></td><td style="display: none;"><div class="precio_unitario">' . $linea->precio_unitario . '</div></td><td style="display: none;"><div class="base_impuesto">' . $linea->base_impuesto . '</div></td><td style="display: none;"><div class="tasa_impuesto">' . $linea->tasa_impuesto . '</div></td><td style="display: none;"><div class="valor_impuesto">' . $linea->valor_impuesto . '</div></td><td style="display: none;"><div class="base_impuesto_total">' . $linea->base_impuesto_total . '</div></td><td style="display: none;"><div class="cantidad">' . $linea->cantidad . '</div></td><td style="display: none;"><div class="precio_total">' . $linea->precio_total . '</div></td><td style="display: none;"><div class="tasa_descuento">' . $linea->tasa_descuento . '</div></td><td style="display: none;"><div class="valor_total_descuento">' . $linea->valor_total_descuento . '</div></td><td> &nbsp; </td><td> <span style="background-color:#F7B2A3;">' . $linea->inv_producto_id . '</span> <div class="lbl_producto_descripcion" style="display: inline;"> ' . $linea->item->descripcion . ' </div> </td><td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' . $linea->cantidad . '</div> </div>  (<div class="lbl_producto_unidad_medida" style="display: inline;">' . $linea->item->unidad_medida1 . '</div>) </td><td>  <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar." id="elemento_modificar_precio_unitario"> ' . $linea->precio_unitario . '</div></div></td><td>' . $linea->tasa_descuento . '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' . number_format($linea->valor_total_descuento, '0', ',', '.') . '</div> ) </td><td><div class="lbl_tasa_impuesto" style="display: inline;">' . $linea->tasa_impuesto . '%</div></td><td> <div class="lbl_precio_total" style="display: inline;">$ ' . number_format($linea->precio_total, '0', ',', '.') . ' </div> </td>';
            
            $cuerpo_tabla_lineas_registros .= '<td>';
            
            if ( !$user->can('bloqueo_eliminar_lineas_al_facturar_pedido')) {
                $cuerpo_tabla_lineas_registros .= '<button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button>';
             }
            
            $cuerpo_tabla_lineas_registros .= '</td></tr>';
            
            $i++;
        }

        $cuerpo_tabla_lineas_registros .= '</tbody>';

        return $cuerpo_tabla_lineas_registros;
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

        $plantilla_pedido_pos_default = 'plantilla_pedido_pos';

        $datos_factura = (object)[
            'core_tipo_transaccion_id' => '',
            'lbl_consecutivo_doc_encabezado' => '',
            'lbl_fecha' => '',
            'lbl_hora' => '',
            'lbl_condicion_pago' => '',
            'lbl_fecha_vencimiento' => '',
            'lbl_descripcion_doc_encabezado' => '',
            'lbl_total_factura' => '',
            'lbl_total_propina' => '',
            'total_factura_mas_propina' => '',
            'lbl_total_datafono' => '',
            'total_factura_mas_datafono' => '',
            'lbl_ajuste_al_peso' => '',
            'lbl_total_recibido' => '0',
            'lbl_total_cambio' => '',
            'lbl_creado_por_fecha_y_hora' => '',
            'lineas_registros' => '',
            'lineas_impuesto' => ''
        ];

        $cliente = $pdv->cliente;
        $tipo_doc_app = $this->transaccion->tipos_documentos->first();
        //$tipo_doc_app = $pdv->tipo_doc_app;
        $pdv_descripcion = $pdv->descripcion;

        return View::make('ventas_pos.pedidos.plantilla_pedido_pos', compact('empresa', 'pdv_descripcion', 'cliente', 'tipo_doc_app', 'plantilla_pedido_pos_default','datos_factura'))->render();
    }

    public function get_total_factura_from_arr_lineas_registros($lineas_registros)
    {
        $total_factura = 0;
        foreach ($lineas_registros as $linea) {
            $total_factura += (float)$linea->precio_total;
        }
        return $total_factura;
    }

    public function get_todos_los_pedidos_mesero_para_la_mesa($pedido)
    {
        return VtasPedido::where(
                            [
                                ['cliente_id','=',$pedido->cliente_id],
                                ['vendedor_id','=',$pedido->vendedor_id],
                                ['estado','=','Pendiente']
                            ]
                        )
                ->get();
    }

    public function get_productos($pdv,$productos)
    {
        $items_en_lista_precios = ListaPrecioDetalle::where('lista_precios_id',$pdv->cliente->lista_precios_id)->get()->pluck('inv_producto_id')->toArray();

        $productosTemp = null;
        foreach ($productos as $pr)
        {
            $grupo_inventario = InvGrupo::find($pr->inv_grupo_id);

            if (!$grupo_inventario->mostrar_en_pagina_web) {
                continue;
            }

            if ((int)config('ventas_pos.mostrar_solo_items_con_precios_en_lista_cliente_default')) {
                if (!in_array($pr->id,$items_en_lista_precios)) {
                    continue;
                }
            }            
            
            if ( is_null($grupo_inventario) )
            {
                return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', 'El producto ' . $pr->descripcion . ' no tiene un grupo de inventario válido.' );
            }

            $pr->categoria = $grupo_inventario->descripcion;
            $productosTemp[$pr->categoria][] = $pr;
        }

        return $productosTemp;
    }

    public function ajustar_campos($lista_campos,$pdv,$vendedor)
    {
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, $cantidad_campos, 'create', null);

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++)
        {
            switch ($lista_campos[$i]['name']) {

                case 'cliente_input':
                    $lista_campos[$i]['value'] = $pdv->cliente->tercero->descripcion;
                    break;

                case 'vendedor_id':
                    $lista_campos[$i]['value'] = [$vendedor->id];
                    break;

                case 'forma_pago':
                    $lista_campos[$i]['value'] = $pdv->cliente->forma_pago( date('Y-m-d') );
                    break;

                case 'fecha':
                    $lista_campos[$i]['value'] = $pdv->ultima_fecha_apertura();
                    break;

                case 'fecha_entrega':
                    $lista_campos[$i]['value'] =date('Y-m-d');
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$i]['value'] = $pdv->cliente->fecha_vencimiento_pago( $pdv->ultima_fecha_apertura() );
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$i]['opciones'] = [$pdv->bodega_default_id => $pdv->bodega->descripcion];
                    break;
                            
                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }

    public function verificar_datos_por_defecto( $pdv )
    {
        if ( is_null( $pdv->cliente ) ) {
            return 'El punto de ventas NO tiene asociado un Cliente por defecto.';
        }

        if ( is_null( $pdv->bodega ) ) {
            return 'El punto de ventas NO tiene asociada una Bodega por defecto.';
        }

        if ( is_null( $pdv->caja ) ) {
            return 'El punto de ventas NO tiene asociada una Caja por defecto.';
        }

        if ( is_null( $pdv->cajero ) ) {
            return 'El punto de ventas NO tiene asociado un Cajero por defecto.';
        }

        if ( is_null( $pdv->tipo_doc_app ) ) {
            return 'El punto de ventas NO tiene asociado un Tipo de documento por defecto.';
        }

        return 'ok';
    }    

    public function consultar_mis_pedidos_pendientes($pdv_id)
    {
        $creado_por = Auth::user()->email;

        $encabezados_documentos = VtasDocEncabezado::where([
            ['estado', '=', 'Pendiente'],
            ['creado_por', '=', $creado_por]
        ])->get();

        $view = Input::get('view');

        $tabla_encabezados_documentos = View::make( 'ventas_pos.pedidos.tabla_encabezados_documentos', compact( 'encabezados_documentos', 'view', 'pdv_id' ) )->render();
        
        return $tabla_encabezados_documentos;

    }

    public function cargar_pedido($pedido_id)
    {
        // DATOS DE LINEAS DE REGISTROS DEL PEDIDO
        $pedido = VtasPedido::find( $pedido_id );

        $pdv = Pdv::find( Input::get('pdv_id') );

        $numero_lineas = count($pedido->lineas_registros);

        $cliente = $pedido->cliente;
        
        $inv_bodega_id = $cliente->inv_bodega_id;
        if ($pdv != null) {
            $inv_bodega_id = $pdv->bodega_default_id;
        }

        $cliente->cliente_id = $cliente->id;
        $cliente->descripcion = $cliente->tercero->descripcion;
        $cliente->numero_identificacion = $cliente->tercero->numero_identificacion;
        $cliente->direccion1 = $cliente->tercero->direccion1;
        $cliente->telefono1 = $cliente->tercero->telefono1;
        $cliente->email = $cliente->tercero->email;

        $cliente->vendedor = $pedido->vendedor;
        $cliente->vendedor_id = $pedido->vendedor->id;
        $cliente->vendedor_descripcion = $pedido->vendedor->tercero->descripcion;

        $cliente->inv_bodega_id = $inv_bodega_id;

        $cliente->dias_plazo = $cliente->condicion_pago->dias_plazo;

        $vendedor = $pedido->vendedor;
        
        if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
            $todas_las_lineas_registros = $this->unificar_lineas_registros_pedidos($pedido);

            $lineas_registros = $this->armar_cuerpo_tabla_lineas_registros($todas_las_lineas_registros);
        }else{
            $lineas_registros = $this->armar_cuerpo_tabla_lineas_registros($pedido->lineas_registros);
        }

        $html = '<div class="list-group">';
        $html .= (new CustomerServices())->get_linea_item_sugerencia( $cliente, 'active', true, 1 );
        $html .= '</div>';

        return response()->json([
            'pedido' => $pedido,
            'numero_lineas' => $numero_lineas,
            'cliente' => $html,
            'vendedor' => $vendedor,
            'lineas_registros' => $lineas_registros,
            'url_cancelar' => url('/').  '/pos_factura/create?id=20&id_modelo=230&id_transaccion=47&pdv_id=' . Input::get('pdv_id') . '&action=create'
        ]);
    }
}