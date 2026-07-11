<?php 

namespace App\VentasPos\Services;

use App\Core\EncabezadoDocumentoTransaccion;
use App\Contabilidad\Impuesto;
use App\FacturacionElectronica\Services\DocumentHeaderService;
use App\Inventarios\Services\InvDocumentsService;
use App\Ventas\Cliente;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\VtasMovimiento;
use App\VentasPos\DocRegistro;
use App\VentasPos\Movimiento;
use App\VentasPos\Pdv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicingService
{
    public function almacenar_factura_electronica( $request )
    {
        // Encabezado
        $doc_encabezado = (new DocumentHeaderService())->store_invoice( $request, 0 );

        $doc_encabezado->valor_total = $doc_encabezado->lineas_registros->sum('precio_total');
        $doc_encabezado->save();

        return $doc_encabezado;
    }

    public function almacenar_remision_factura( $encabezado_factura, $inv_bodega_id )
    {
        $obj_inv_serv = new InventoriesServices();
        $buscar_bodega_cocina = !is_null($encabezado_factura->pdv)
                                && (int)$encabezado_factura->pdv->crear_ensamble_de_recetas === 1;
        $doc_remision = $obj_inv_serv->create_delivery_note_from_invoice( $encabezado_factura, $inv_bodega_id, $buscar_bodega_cocina ); // Sin contabilizar

        $obj_inv_doc_serv = new InvDocumentsService();
        $obj_inv_doc_serv->store_accounting_doc_head( $doc_remision->id, '' );

        return $doc_remision;
    }

    public function almacenar_factura_pos( $request )
    {
        $this->aplicar_fechas_factura_pos_por_defecto($request);

        $lineas_registros = json_decode($request->lineas_registros);

        $this->validar_lineas_registros_pos($lineas_registros, $request);
        $this->quitar_medios_recaudo_repetidos_en_exceso($request, $lineas_registros);

        // Encabezado
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );

        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

        // Lineas de registros
        $this->crear_registros_documento_pos($request, $doc_encabezado, $lineas_registros);  
        
        $doc_encabezado->valor_total = $doc_encabezado->lineas_registros->sum('precio_total');
        $doc_encabezado->save();

        // Movimiento
        $this->crear_movimiento_pos($doc_encabezado);
        
        $obj_acumm_serv = new AccumulationService( $doc_encabezado->pdv_id );
        
        // Realizar preparaciones de recetas
        $obj_acumm_serv->hacer_preparaciones_recetas( 'Creado por factura POS ' . $doc_encabezado->get_label_documento(), $doc_encabezado->fecha, $doc_encabezado->id );

        // Realizar desarme automático
        $obj_acumm_serv->hacer_desarme_automatico( 'Creado por factura POS ' . $doc_encabezado->get_label_documento(), $doc_encabezado->fecha);

        $obj_acumm_serv->accumulate_one_invoice($doc_encabezado->id);

        return $doc_encabezado;
    }

    protected function aplicar_fechas_factura_pos_por_defecto(Request $request)
    {
        $fecha = trim((string)$request->get('fecha', ''));
        $pdv = Pdv::find((int)$request->get('pdv_id', 0));

        if ($fecha == '') {
            $fecha = date('Y-m-d');
            if (!is_null($pdv) && config('ventas_pos.asignar_fecha_apertura_a_facturas')) {
                $fecha = $pdv->ultima_fecha_apertura(false);
            }
            $request->merge(['fecha' => $fecha]);
        }

        if (trim((string)$request->get('fecha_vencimiento', '')) != '') {
            return;
        }

        $fecha_vencimiento = $fecha;
        if (!is_null($pdv) && !is_null($pdv->cliente)) {
            $fecha_vencimiento = $pdv->cliente->fecha_vencimiento_pago($fecha);
        }

        $request->merge(['fecha_vencimiento' => $fecha_vencimiento]);
    }

    public function crear_registros_documento_pos(Request $request, $doc_encabezado, array $lineas_registros)
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros

        $this->validar_lineas_registros_pos($lineas_registros, $request);

        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            if ( (int)$lineas_registros[$i]->inv_producto_id == 0)
            {
                continue; // Evitar guardar registros con productos NO validos
            }
            
            $impuesto_id = 0;
            if (property_exists($lineas_registros[$i], 'impuesto_id')) {
                $impuesto_id = (int)$lineas_registros[$i]->impuesto_id;
            } elseif (property_exists($lineas_registros[$i], 'tasa_impuesto')) {
                $tasa_impuesto = (float)$lineas_registros[$i]->tasa_impuesto;
                if ($tasa_impuesto >= 0) {
                    $impuesto_id = (int)Impuesto::where('tasa_impuesto', $tasa_impuesto)->min('id');
                }
            }

            $linea_datos = ['vtas_motivo_id' => (int)$request->inv_motivo_id] +
                            ['inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id] +
                            ['inv_bodega_id' => $this->resolveLineWarehouseId($lineas_registros[$i], $doc_encabezado, $request)] +
                            ['impuesto_id' => $impuesto_id] +
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
                            ['estado' => 'Contabilizado'] +
                            ['vtas_pos_doc_encabezado_id' => $doc_encabezado->id];

            DocRegistro::create($linea_datos);
        }
    }

    public function validar_lineas_registros_pos($lineas_registros, Request $request = null)
    {
        if (!is_array($lineas_registros) || count($lineas_registros) == 0) {
            throw new \InvalidArgumentException('No hay líneas de productos para guardar la factura.');
        }

        $precios_lista = $this->get_precios_lista_para_validar($lineas_registros, $request);

        foreach ($lineas_registros as $index => $linea) {
            if ((int)$this->get_line_value($linea, 'inv_producto_id', 0) == 0) {
                continue;
            }

            $this->validar_totales_linea($linea, $index + 1);
            $this->validar_precio_lista_linea($linea, $index + 1, $precios_lista);
        }
    }

    protected function get_precios_lista_para_validar(array $lineas_registros, Request $request = null)
    {
        if (!$this->debe_validar_precio_lista($request)) {
            return [];
        }

        $lista_precios_id = $this->get_lista_precios_id_para_validar($request);
        $fecha = trim((string)$request->get('fecha', ''));

        if ($lista_precios_id <= 0 || $fecha == '') {
            return [];
        }

        $productos_ids = [];
        foreach ($lineas_registros as $linea) {
            $producto_id = (int)$this->get_line_value($linea, 'inv_producto_id', 0);
            if ($producto_id > 0) {
                $productos_ids[$producto_id] = $producto_id;
            }
        }

        if (count($productos_ids) == 0) {
            return [];
        }

        $registros = ListaPrecioDetalle::where('lista_precios_id', $lista_precios_id)
            ->where('fecha_activacion', '<=', $fecha)
            ->whereIn('inv_producto_id', array_values($productos_ids))
            ->orderBy('fecha_activacion', 'DESC')
            ->get();

        $precios = [];
        foreach ($registros as $registro) {
            $producto_id = (int)$registro->inv_producto_id;
            if (!isset($precios[$producto_id])) {
                $precios[$producto_id] = (float)$registro->precio;
            }
        }

        return $precios;
    }

    protected function debe_validar_precio_lista(Request $request = null)
    {
        if (is_null($request) || !(int)config('ventas_pos.validar_precio_lista_al_guardar_factura_pos', 1)) {
            return false;
        }

        if ((int)$request->get('pedido_id', 0) > 0) {
            return false;
        }

        if (Auth::check()) {
            if (!Auth::user()->can('bloqueo_cambiar_precio_unitario')) {
                return false;
            }

            if (Auth::user()->can('editar_precio_total_en_linea_registro_factura_pos')) {
                return false;
            }
        }

        return true;
    }

    protected function validar_precio_lista_linea($linea, $numero_linea, array $precios_lista)
    {
        $producto_id = (int)$this->get_line_value($linea, 'inv_producto_id', 0);

        if ($producto_id <= 0 || !isset($precios_lista[$producto_id])) {
            return;
        }

        $precio_lista = (float)$precios_lista[$producto_id];
        if ($precio_lista <= 0) {
            return;
        }

        $precio_unitario = (float)$this->get_line_value($linea, 'precio_unitario', 0);
        if (abs($precio_lista - $precio_unitario) <= 1.0) {
            return;
        }

        throw new \InvalidArgumentException(
            'La línea ' . $numero_linea . ' tiene precio desactualizado. Precio enviado: $' .
            number_format($precio_unitario, 0, ',', '.') . '. Precio vigente: $' .
            number_format($precio_lista, 0, ',', '.') . '. Recargue el producto y vuelva a guardar.'
        );
    }

    protected function get_lista_precios_id_para_validar(Request $request)
    {
        $cliente_id = (int)$request->get('cliente_id', 0);
        if ($cliente_id > 0) {
            $cliente = Cliente::find($cliente_id);
            if (!is_null($cliente)) {
                return (int)$cliente->lista_precios_id;
            }
        }

        return (int)$request->get('lista_precios_id', 0);
    }

    protected function quitar_medios_recaudo_repetidos_en_exceso(Request $request, array $lineas_registros)
    {
        $lineas_recaudos = json_decode((string)$request->lineas_registros_medios_recaudos, true);
        if (!is_array($lineas_recaudos) || count($lineas_recaudos) < 2) {
            return;
        }

        $total_documento = $this->get_total_documento_pos($request, $lineas_registros);
        $total_recaudos = 0;
        foreach ($lineas_recaudos as $linea) {
            $total_recaudos += $this->parse_valor_recaudo(isset($linea['valor']) ? $linea['valor'] : 0);
        }

        $tolerancia = 1.0;
        if ($total_recaudos <= ($total_documento + $tolerancia)) {
            return;
        }

        $vistos = [];
        $lineas_filtradas = [];
        foreach ($lineas_recaudos as $linea) {
            if (!is_array($linea)) {
                $lineas_filtradas[] = $linea;
                continue;
            }

            $valor_linea = $this->parse_valor_recaudo(isset($linea['valor']) ? $linea['valor'] : 0);
            $clave = implode('|', [
                isset($linea['teso_medio_recaudo_id']) ? $linea['teso_medio_recaudo_id'] : '',
                isset($linea['teso_motivo_id']) ? $linea['teso_motivo_id'] : '',
                isset($linea['teso_caja_id']) ? $linea['teso_caja_id'] : '',
                isset($linea['teso_cuenta_bancaria_id']) ? $linea['teso_cuenta_bancaria_id'] : '',
                number_format($valor_linea, 2, '.', '')
            ]);

            if (isset($vistos[$clave]) && ($total_recaudos - $valor_linea) >= ($total_documento - $tolerancia)) {
                $total_recaudos -= $valor_linea;
                continue;
            }

            $vistos[$clave] = true;
            $lineas_filtradas[] = $linea;
        }

        if (count($lineas_filtradas) != count($lineas_recaudos)) {
            $request->merge([
                'lineas_registros_medios_recaudos' => json_encode(array_values($lineas_filtradas))
            ]);
        }
    }

    protected function get_total_documento_pos(Request $request, array $lineas_registros)
    {
        $total = 0;
        foreach ($lineas_registros as $linea) {
            $total += (float)$this->get_line_value($linea, 'precio_total', 0);
        }

        return $total
            + (float)$request->get('valor_ajuste_al_peso', 0)
            + (float)$request->get('valor_total_bolsas', 0);
    }

    protected function parse_valor_recaudo($valor)
    {
        $valor = trim(str_replace(['$', ' '], '', (string)$valor));
        if ($valor == '') {
            return 0;
        }

        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (preg_match('/\.\d{3}$/', $valor)) {
            $valor = str_replace('.', '', $valor);
        }

        return (float)$valor;
    }

    protected function validar_totales_linea($linea, $numero_linea)
    {
        $cantidad = (float)$this->get_line_value($linea, 'cantidad', 0);
        $precio_unitario = (float)$this->get_line_value($linea, 'precio_unitario', 0);
        $precio_total = (float)$this->get_line_value($linea, 'precio_total', 0);
        $tasa_descuento = (float)$this->get_line_value($linea, 'tasa_descuento', 0);
        $valor_total_descuento = (float)$this->get_line_value($linea, 'valor_total_descuento', 0);
        $base_impuesto = (float)$this->get_line_value($linea, 'base_impuesto', 0);
        $valor_impuesto = (float)$this->get_line_value($linea, 'valor_impuesto', 0);
        $base_impuesto_total = (float)$this->get_line_value($linea, 'base_impuesto_total', 0);

        $tolerancia = 1.0;

        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La línea ' . $numero_linea . ' tiene cantidad inválida.');
        }

        if ($precio_total < 0 || $base_impuesto_total < 0 || $valor_impuesto < 0) {
            throw new \InvalidArgumentException('La línea ' . $numero_linea . ' tiene valores negativos no permitidos.');
        }

        $descuento_esperado = 0;
        if ($tasa_descuento > 0) {
            $descuento_esperado = round($precio_unitario * $cantidad * $tasa_descuento / 100, 2);
        } else {
            $descuento_esperado = round($valor_total_descuento, 2);
        }

        $precio_total_esperado = round(($precio_unitario * $cantidad) - $descuento_esperado, 2);
        if (abs($precio_total_esperado - $precio_total) > $tolerancia) {
            throw new \InvalidArgumentException('La línea ' . $numero_linea . ' no cuadra: precio unitario por cantidad no coincide con el total.');
        }

        $total_linea_calculado = round($base_impuesto_total + ($valor_impuesto * $cantidad), 2);
        if (abs($total_linea_calculado - $precio_total) > $tolerancia) {
            throw new \InvalidArgumentException('La línea ' . $numero_linea . ' no cuadra: base más impuesto no coincide con el total.');
        }

        if (abs(round($base_impuesto * $cantidad, 2) - round($base_impuesto_total, 2)) > $tolerancia) {
            throw new \InvalidArgumentException('La línea ' . $numero_linea . ' no cuadra: base unitaria por cantidad no coincide con la base total.');
        }
    }

    protected function get_line_value($linea, $property, $default = 0)
    {
        if (is_object($linea) && property_exists($linea, $property)) {
            return $linea->{$property};
        }

        if (is_array($linea) && array_key_exists($property, $linea)) {
            return $linea[$property];
        }

        return $default;
    }

    protected function resolveLineWarehouseId($linea, $invoice = null, Request $request = null)
    {
        $inv_bodega_id = 0;

        if (is_object($linea) && isset($linea->inv_bodega_id)) {
            $inv_bodega_id = (int)$linea->inv_bodega_id;
        }

        if ($inv_bodega_id <= 0) {
            $inv_bodega_id = (int)$this->get_line_value($linea, 'inv_bodega_id', 0);
        }

        if ($inv_bodega_id <= 0 && !is_null($request)) {
            $inv_bodega_id = (int)$request->get('inv_bodega_id', 0);
        }

        if ($inv_bodega_id <= 0 && !is_null($invoice) && !is_null($invoice->pdv)) {
            $inv_bodega_id = (int)$invoice->pdv->bodega_default_id;
        }

        if ($inv_bodega_id <= 0 && !is_null($invoice) && (int)$invoice->pdv_id > 0) {
            $pdv = Pdv::find((int)$invoice->pdv_id);
            if (!is_null($pdv)) {
                $inv_bodega_id = (int)$pdv->bodega_default_id;
            }
        }

        if ($inv_bodega_id <= 0) {
            $inv_bodega_id = (int)config('ventas.inv_bodega_id');
        }

        if ($inv_bodega_id <= 0) {
            throw new \InvalidArgumentException('No se pudo determinar la bodega de inventarios para la factura POS.');
        }

        return $inv_bodega_id;
    }

    public function crear_movimiento_pos($invoice)
    {
        $datos = $invoice->toArray();
        unset($datos['id']);
        
        $cliente = $invoice->cliente;

        $datos['zona_id'] = $cliente->zona_id;
        $datos['clase_cliente_id'] = $cliente->clase_cliente_id;
        $datos['equipo_ventas_id'] = $invoice->vendedor->equipo_ventas_id;
        $datos['estado'] = 'Contabilizado';

        $lineas_registros = $invoice->lineas_registros;
        foreach ($lineas_registros as $linea)
        {
            $linea_datos = $linea->toArray();
            $linea_datos['inv_bodega_id'] = $this->resolveLineWarehouseId($linea, $invoice);

            // Movimiento POS
            Movimiento::create( $datos + $linea_datos );
        }
    }
}
