<?php 

namespace App\Ventas\Services;

use App\Contabilidad\ContabMovimiento;
use App\Ventas\Cliente;
use Illuminate\Support\Facades\Auth;

class CustomerServices
{
    public function preparar_datos($datos)
    {
        if( !isset($datos['numero_identificacion']) )
        {
            $datos['numero_identificacion'] = abs( crc32( uniqid() ) ); // Cedula de ciudadania
        } 

        $descripcion = '';
        // Almacenar datos básicos (Tercero)
        if( isset($datos['apellido1']) && isset($datos['apellido2']) && isset($datos['nombre1']) && isset($datos['otros_nombres']))
        {
            $descripcion = $datos['apellido1']." ".$datos['apellido2']." ".$datos['nombre1']." ".$datos['otros_nombres'];
        }

        if( isset($datos['descripcion']) )
        {
            $descripcion = $datos['descripcion'];
        } 

        if (isset($datos['razon_social'])) {
            if ( $datos['razon_social'] != '' && $descripcion == '' )
            {
                $descripcion = $datos['razon_social'];
            }
        }

        $datos['descripcion'] = $descripcion;

        if( !isset($datos['nombre1']))
        {
            $datos['nombre1'] = $descripcion;
        }

        if( !isset($datos['id_tipo_documento_id']) )
        {
            $datos['id_tipo_documento_id'] = 13; // Cedula de ciudadania
        } 

        if( !isset($datos['tipo']) )
        {
            $datos['tipo'] = 'Persona natural';
        } 

        if( !isset($datos['codigo_ciudad']) )
        {
            $datos['codigo_ciudad'] = '16920001'; // Valledupar
        } 

        if( !isset($datos['clase_cliente_id']) )
        {
            $datos['clase_cliente_id'] = '1';
        } 

        if( !isset($datos['zona_id']) )
        {
            $datos['zona_id'] = '1';
        } 

        if( !isset($datos['vendedor_id']) )
        {
            $datos['vendedor_id'] = (int)config('ventas.vendedor_id');
        } 

        if( !isset($datos['inv_bodega_id']) )
        {
            $datos['inv_bodega_id'] = (int)config('inventarios.item_bodega_principal_id');
        } 

        if( !isset($datos['lista_precios_id']) )
        {
            $datos['lista_precios_id'] = (int)config('ventas.lista_precios_id');
        } 

        if( !isset($datos['lista_descuentos_id']) )
        {
            $datos['lista_descuentos_id'] = (int)config('ventas.lista_descuentos_id');
        } 

        if( !isset($datos['liquida_impuestos']) )
        {
            $datos['liquida_impuestos'] = '1';
        } 

        if( !isset($datos['condicion_pago_id']) )
        {
            $datos['condicion_pago_id'] = '1';
        } 

        if( !isset($datos['estado']) )
        {
            $datos['estado'] = 'Activo';
        } 

        if( !isset($datos['creado_por']) )
        {
            $datos['creado_por'] = Auth::user()->email;
        } 
        
        return $datos;
    }

    public function get_linea_item_sugerencia( Cliente $linea, $clase, $primer_item, $ultimo_item )
    {
        $descripcion = $linea->descripcion;
        if ( $linea->razon_social != '' ) {
            $descripcion .=  ' ('. $linea->razon_social . ')';
        }

        $html = '<a class="list-group-item list-group-item-cliente '.$clase.'" data-cliente_id="'.$linea->cliente_id.
                                '" data-primer_item="'.$primer_item.
                                '" data-accion="na" '.
                                '" data-ultimo_item="'.$ultimo_item; // Esto debe ser igual en todas las busquedas

        $html .=            '" data-nombre_cliente="'.$descripcion.
                            '" data-zona_id="'.$linea->zona_id.
                            '" data-clase_cliente_id="'.$linea->clase_cliente_id.
                            '" data-liquida_impuestos="'.$linea->liquida_impuestos.
                            '" data-core_tercero_id="'.$linea->core_tercero_id.
                            '" data-direccion1="'.$linea->direccion1.
                            '" data-telefono1="'.$linea->telefono1.
                            '" data-numero_identificacion="'.$linea->numero_identificacion.
                            '" data-vendedor_id="'.$linea->vendedor_id.
                            '" data-vendedor_descripcion="'.$linea->vendedor->tercero->descripcion.
                            '" data-equipo_ventas_id="0'.
                            '" data-inv_bodega_id="'.$linea->inv_bodega_id.
                            '" data-email="'.$linea->email.
                            '" data-dias_plazo="'.$linea->dias_plazo.
                            '" data-lista_precios_id="'.$linea->lista_precios_id.
                            '" data-lista_descuentos_id="'.$linea->lista_descuentos_id.
                            '" > '.$descripcion.' ('.number_format($linea->numero_identificacion,0,',','.').') </a>';
                            
        return $html;
    }
}