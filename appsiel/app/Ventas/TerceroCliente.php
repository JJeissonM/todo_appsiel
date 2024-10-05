<?php

namespace App\Ventas;

class TerceroCliente extends Cliente
{
    protected $table = 'vtas_clientes';

    public static function opciones_campo_select()
    {
        $opciones = Cliente::leftJoin('core_terceros','core_terceros.id','=','vtas_clientes.core_tercero_id')
                    ->where('vtas_clientes.estado','Activo')
                    ->select(
                        'vtas_clientes.core_tercero_id',
                        'core_terceros.descripcion',
                        'core_terceros.numero_identificacion',
                        'core_terceros.razon_social'
                    )
                    ->orderby('core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $descripcion = $opcion->descripcion;
            if ( $opcion->razon_social != '' ) {
                $descripcion .=  ' ('. $opcion->razon_social . ')';
            }

            $vec[$opcion->core_tercero_id] = $opcion->numero_identificacion . ' ' . $descripcion;
        }

        return $vec;
    }
}
