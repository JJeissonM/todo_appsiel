<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;


use Spatie\Permission\Models\Permission;


use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvCostoPromProducto;

class BasculaDibalController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Obtinene los productos que aún no han sido facturados 
     *
     */
    public function get_productos_por_facturar()
    {
        $numero_linea = Input::get('numero_linea');
        $hay_productos = Input::get('hay_productos');
        $tickets = DB::connection('dfs')->table('dat_ticket_linea')->where( 'IdBalanzaMaestra', Input::get('bascula_id') )->get();

        $lineas_registros = '';
        $btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
        foreach ($tickets as $linea) 
        {
            $inv_producto_id = $linea->IdArticulo;
            $producto = $this->consultar_existencia_producto(Input::get('cliente_id'), Input::get('bodega_id'), $inv_producto_id );
            //print_r( $producto );
            //echo "<br><br>";
            $cantidad = $linea->Peso;

            // Comportamiento es Tipo de articulo: 1=Pesado, 0=Unitario 
            if ( !$linea->Comportamiento ) {
                $cantidad = $linea->Peso * 1000;
            }
            
            if( !empty($producto) )
            {

                $base_impuesto_total = $producto['base_impuesto'] * $cantidad;
                $precio_total = $producto['precio_venta'] * $cantidad;
                $costo_total = $producto['costo_promedio'] * $cantidad;


                $lineas_registros .= '<tr class="linea_registro" data-numero_linea="'.$numero_linea.'">
                        <td style="display: none;"><div class="inv_motivo_id">'. Input::get('inv_motivo_id') .'</div></td>
                        <td style="display: none;"><div class="inv_bodega_id">'. Input::get('bodega_id') .'</div></td>
                        <td style="display: none;"><div class="inv_producto_id">'. $inv_producto_id .'</div></td>
                        <td style="display: none;"><div class="costo_unitario">'. $producto['costo_promedio'] .'</div></td>
                        <td style="display: none;"><div class="precio_unitario">'. $producto['precio_venta'] .'</div></td>
                        <td style="display: none;"><div class="base_impuesto">'. $producto['base_impuesto'] .'</div></td>
                        <td style="display: none;"><div class="tasa_impuesto">'. $producto['tasa_impuesto'] .'</div></td>
                        <td style="display: none;"><div class="valor_impuesto">'. $producto['valor_impuesto'] .'</div></td>
                        <td style="display: none;"><div class="base_impuesto_total">'. $base_impuesto_total .'</div></td>
                        <td style="display: none;"><div class="cantidad">'.$cantidad.'</div></td>
                        <td style="display: none;"><div class="costo_total">'.$costo_total .'</div></td>
                        <td style="display: none;"><div class="precio_total">'.$precio_total .'</div></td>
                        <td> &nbsp; </td>
                        <td>'.$producto['descripcion'] . '</td>
                        <td>'.Input::get('motivo_descripcion') . '</td>
                        <td>'.$producto['existencia_actual'] . '</td>
                        <td>$'.number_format($producto['precio_venta'],0,',','.') . '</td>
                        <td>'.$producto['tasa_impuesto'] . '% </td>
                        <td>'.$cantidad . ' </td>
                        <td>'.number_format($precio_total,0,',','.') . ' </td>
                        <td>'.$btn_borrar . '</td></tr>';

                $numero_linea++;
                $hay_productos++;
            }
            
        }

        //print_r($tickets);
        return [$lineas_registros,$numero_linea,$hay_productos];

        /*$tabla = '<table border="1" style="border-collapse: collapse;">
                    <tr>
                        <td>IdBalanzaMaestra</td>
                        <td>IdTicket</td>
                        <td>Facturada</td>
                        <td>IdArticulo</td>
                        <td>Descripcion</td>
                        <td>Peso</td>
                        <td>Precio</td>
                        <td>Importe</td>
                        <td>TimeStamp</td>
                        </tr>';
        foreach ($tickets as $linea) 
        {
            $tabla .= '<tr>
                        <td>'.$linea->IdBalanzaMaestra.'</td>
                        <td>'.$linea->IdTicket.'</td>
                        <td>'.$linea->Facturada.'</td>
                        <td>'.$linea->IdArticulo.'</td>
                        <td>'.$linea->Descripcion.'</td>
                        <td>'.$linea->Peso.'</td>
                        <td>'.$linea->Precio.'</td>
                        <td>'.$linea->Importe.'</td>
                        <td>'.$linea->TimeStamp.'</td>
                    </tr>';
            //print_r( $linea );
            //echo '<br>';
        }
        $tabla .= '</table>';

        echo $tabla;
        */

    }



    // Parámetro enviados por GET
    public function consultar_existencia_producto($cliente_id, $bodega_id, $producto_id )
    {
        
        $producto = InvProducto::leftJoin('contab_impuestos','contab_impuestos.id','=','inv_productos.impuesto_id')->where('inv_productos.id', $producto_id )->select('inv_productos.id','inv_productos.tipo','inv_productos.descripcion','inv_productos.precio_compra','inv_productos.precio_venta','contab_impuestos.tasa_impuesto')->get()->first();

        //dd($producto->toArray());

        if ( !is_null($producto) ) {
            $producto = $producto->toArray(); // Se convierte en array para manipular facilmente sus campos 
        }else{
            $producto = [];
        }

        // $producto es un array
        if( !empty($producto) )
        {
            $costo_promedio = InvCostoPromProducto::where('inv_bodega_id','=',$bodega_id)
                                    ->where('inv_producto_id','=',$producto['id'])
                                    ->value('costo_promedio');
            if ( ! ($costo_promedio>0) ) 
            {
                $costo_promedio = 0;
            }


            /*
                El precio de venta se debería traer de la lista de precios.

                Falta el manejo de los descuentos.

            */

            $tasa_impuesto = (float)$producto['tasa_impuesto'];

            if ( is_null($tasa_impuesto) ) 
            {
                $tasa_impuesto = 0;
            }
            
            $base_impuesto = ( (float)$producto['precio_venta'] ) / ( 1 + $tasa_impuesto / 100 );
            $valor_impuesto = (float)$producto['precio_venta'] - $base_impuesto;

            /*
                PENDIENTE: VALIDACIONES DE FECHA


            */

            // Obtener existencia actual
            $existencia_actual = InvMovimiento::get_existencia_actual( $producto['id'], $bodega_id, Input::get('fecha') );

            $producto = array_merge($producto,['costo_promedio'=>$costo_promedio]);

            $producto = array_merge($producto, [ 'existencia_actual' => $existencia_actual ],
                                                [ 'tipo' => $producto['tipo'] ],
                                                [ 'costo_promedio' => $costo_promedio ],
                                                [ 'base_impuesto' => $base_impuesto ],
                                                [ 'tasa_impuesto' => $tasa_impuesto ],
                                                [ 'valor_impuesto' => $valor_impuesto ]
                                    );
        }

        //print_r($producto);
        return $producto;
    }

}