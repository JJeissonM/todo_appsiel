<?php 

namespace App\Ventas\Services;

use App\Contabilidad\ContabMovimiento;

class CustomerServices
{
    public function preparar_datos($datos)
    {
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
            $datos['vendedor_id'] = '1';
        } 

        if( !isset($datos['inv_bodega_id']) )
        {
            $datos['inv_bodega_id'] = '1';
        } 

        if( !isset($datos['lista_precios_id']) )
        {
            $datos['lista_precios_id'] = '1';
        } 

        if( !isset($datos['lista_descuentos_id']) )
        {
            $datos['lista_descuentos_id'] = '1';
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
        
        return $datos;
    }       
}