<?php 

namespace App\VentasPos\Services;

use App\Core\EncabezadoDocumentoTransaccion;
use App\Contabilidad\Impuesto;
use App\FacturacionElectronica\Services\DocumentHeaderService;
use App\Inventarios\Services\InvDocumentsService;
use App\Ventas\VtasMovimiento;
use App\VentasPos\DocRegistro;
use App\VentasPos\Movimiento;
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
        $doc_remision = $obj_inv_serv->create_delivery_note_from_invoice( $encabezado_factura, $inv_bodega_id ); // Sin contabilizar

        $obj_inv_doc_serv = new InvDocumentsService();
        $obj_inv_doc_serv->store_accounting_doc_head( $doc_remision->id, '' );

        return $doc_remision;
    }

    public function almacenar_factura_pos( $request )
    {
        $lineas_registros = json_decode($request->lineas_registros);

        $this->validar_lineas_registros_pos($lineas_registros);

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
        $obj_acumm_serv->hacer_preparaciones_recetas( 'Creado por factura POS ' . $doc_encabezado->get_label_documento(), $doc_encabezado->fecha );

        // Realizar desarme automático
        $obj_acumm_serv->hacer_desarme_automatico( 'Creado por factura POS ' . $doc_encabezado->get_label_documento(), $doc_encabezado->fecha);

        $obj_acumm_serv->accumulate_one_invoice($doc_encabezado->id);

        return $doc_encabezado;
    }

    public function crear_registros_documento_pos(Request $request, $doc_encabezado, array $lineas_registros)
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros

        $this->validar_lineas_registros_pos($lineas_registros);

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

    public function validar_lineas_registros_pos($lineas_registros)
    {
        if (!is_array($lineas_registros) || count($lineas_registros) == 0) {
            throw new \InvalidArgumentException('No hay líneas de productos para guardar la factura.');
        }

        foreach ($lineas_registros as $index => $linea) {
            if ((int)$this->get_line_value($linea, 'inv_producto_id', 0) == 0) {
                continue;
            }

            $this->validar_totales_linea($linea, $index + 1);
        }
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
            // Movimiento POS
            Movimiento::create( $datos + $linea->toArray() );
        }
    }
}
