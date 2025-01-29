<?php 

namespace App\VentasPos\Services;

use App\Inventarios\InvProducto;
use App\Inventarios\Services\RecipeServices;
use App\Ventas\Cliente;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\Vendedor;
use App\VentasPos\Pdv;
use \View;

class CrudService
{
    public function custom_fields_for_edit($lista_campos, $doc_encabezado, $pdv)
    {
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
            switch ($value['name']){

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

        return $lista_campos;
    }

    public function set_catalogos( $pdv_id )
    {
        // El costo promedio del item se llama desde inv_productos
        $pdv = Pdv::find( $pdv_id );
        $datos = [
                    'redondear_centena' => config('ventas_pos.redondear_centena'),
                    'productos' => InvProducto::get_datos_basicos('', 'Activo', null, null),
                    'precios' => ListaPrecioDetalle::get_precios_productos_de_la_lista( $pdv->cliente->lista_precios_id ),
                    'todos_los_precios' => ListaPrecioDetalle::get_precios_para_catalogos_pos(),
                    'descuentos' => ListaDctoDetalle::get_descuentos_productos_de_la_lista( null ),
                    'todos_los_descuentos' => ListaDctoDetalle::get_descuentos_para_catalogos_pos(),
                    'clientes' => Cliente::get_lista_para_catalogos_pos(),
                    'cliente_default' => array_merge( $pdv->cliente->tercero->toArray(), $pdv->cliente->toArray(), ['vendedor_descripcion'=> $pdv->cliente->vendedor->tercero->descripcion] ) ,
                    'forma_pago_default' => $pdv->cliente->forma_pago(),
                    'fecha_vencimiento_default' => $pdv->cliente->fecha_vencimiento_pago( date('Y-m-d') ),
                    'contornos_permitidos' => (new RecipeServices())->get_recetas_items_manejan_contornos(),
                    'vendedores' => Vendedor::get_lista_para_catalogos_pos(),
                ];
        
        return response()->json( $datos );
    }
}