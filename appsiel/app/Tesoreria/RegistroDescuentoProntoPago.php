<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use App\Contabilidad\ContabMovimiento;
use App\Compras\DescuentoProntoPago;
use App\Ventas\DescuentoPpEncabezado;

class RegistroDescuentoProntoPago extends Model
{
    public function almacenar_nuevos_registros( $json_lineas_registros, $doc_encabezado, $tipo )
    {        
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            // Solo se contabiliza el descuento

            $valor_descuento_pronto_pago = (float)$lineas_registros[$i]->valor_descuento_pronto_pago;

            $datos = $doc_encabezado->toArray();

            $movimiento_contable = new ContabMovimiento();

            switch ( $tipo )
            {
                case 'recibido': // CxP (Compras)
                    $descuento = DescuentoProntoPago::find( (int)$lineas_registros[$i]->descuento_pronto_pago_id );
                    $valor_debito = 0;
                    $valor_credito = $valor_descuento_pronto_pago;
                    break;
                case 'concedido': // CxC (Ventas)
                    $descuento = DescuentoPpEncabezado::find( (int)$lineas_registros[$i]->descuento_pronto_pago_id );
                    $valor_debito = $valor_descuento_pronto_pago;
                    $valor_credito = 0;
                    break;
                
                default:
                    # code...
                    break;
            }

            $movimiento_contable->contabilizar_linea_registro( $datos, $descuento->contab_cuenta_id, 'Descuento ' . $tipo, $valor_debito, $valor_credito );
        }
    }
}
