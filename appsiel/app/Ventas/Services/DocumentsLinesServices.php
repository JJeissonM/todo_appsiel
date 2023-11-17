<?php 

namespace App\Ventas\Services;
use Illuminate\Http\Request;

use App\Contabilidad\Impuesto;

use App\Ventas\ListaDctoDetalle;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\VtasDocRegistro;
use Illuminate\Support\Facades\Auth;

class DocumentsLinesServices
{
    public function crear_registros_documento(Request $request, $doc_encabezado, array $lineas_registros)
    {
        $lista_precios_id = $doc_encabezado->cliente->lista_precios_id;
        $lista_descuentos_id = $doc_encabezado->cliente->lista_descuentos_id;

        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            if( !isset($lineas_registros[$i]->inv_motivo_id) )
                $inv_motivo_id = 10;//config('pagina_web.pedidos_inv_motivo_id');
            else
                $inv_motivo_id = $lineas_registros[$i]->inv_motivo_id;

            // Se llama nuevamente el precio de venta para estar SEGURO ( Cuando se hace desde la web )
              $precio_unitario = ListaPrecioDetalle::get_precio_producto( $lista_precios_id, $doc_encabezado->fecha, $lineas_registros[$i]->inv_producto_id );
              $tasa_descuento = ListaDctoDetalle::get_descuento_producto( $lista_descuentos_id, $doc_encabezado->fecha, $lineas_registros[$i]->inv_producto_id );

              $cantidad = (float)$lineas_registros[$i]->cantidad;
              $valor_unitario_descuento = 0;

              if ( isset( $request->url_id ) && !$request->pedido_web)
              {
                // Si el pedido se hace desde el modulo de Ventas o POS
                if ( (int)$request->url_id == 13 || (int)$request->url_id == 20 ) 
                {
                    $tasa_descuento = (float)$lineas_registros[$i]->tasa_descuento;
                    $precio_unitario = (float)$lineas_registros[$i]->precio_unitario;
                    $valor_unitario_descuento = $precio_unitario * ( $tasa_descuento / 100 );
                }
              }

              $precio_venta_unitario = $precio_unitario - $valor_unitario_descuento;
              
              $tasa_impuesto = Impuesto::get_tasa($lineas_registros[$i]->inv_producto_id,0,$doc_encabezado->cliente_id);

              $base_impuesto = $precio_venta_unitario / ( 1 + $tasa_impuesto / 100 );
              $valor_impuesto = $precio_venta_unitario - $base_impuesto;

              $precio_total = $precio_venta_unitario *  $cantidad;

              $linea_datos = ['vtas_motivo_id' =>$inv_motivo_id] +
                          ['inv_producto_id' => $lineas_registros[$i]->inv_producto_id] +
                          ['precio_unitario' => $precio_unitario] +
                          ['cantidad' =>  $cantidad ] +
                          ['cantidad_pendiente' =>  $cantidad ] +
                          ['precio_total' => $precio_total ] +
                          ['base_impuesto' => $base_impuesto] +
                          ['tasa_impuesto' => $tasa_impuesto] +
                          ['valor_impuesto' => $valor_impuesto] +
                          ['base_impuesto_total' => $base_impuesto *  $cantidad ] +
                          [ 'tasa_descuento' => $tasa_descuento ] +
                          [ 'valor_total_descuento' => $valor_unitario_descuento *  $cantidad ] +
                          ['creado_por' => Auth::user()->email] +
                          ['estado' => 'Activo'];

            VtasDocRegistro::create(
                                        ['vtas_doc_encabezado_id' => $doc_encabezado->id] +
                                        $linea_datos
                                    );

            $total_documento += $precio_total;

        } // Fin por cada registro

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->save();
    }        
}