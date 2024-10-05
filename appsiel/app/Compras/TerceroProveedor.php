<?php

namespace App\Compras;

class TerceroProveedor extends Proveedor
{
    protected $table = 'compras_proveedores';

    public static function opciones_campo_select()
    {
        $opciones = Proveedor::leftJoin('core_terceros','core_terceros.id','=','compras_proveedores.core_tercero_id')
                    ->where('compras_proveedores.estado','Activo')
                    ->select(
                        'compras_proveedores.core_tercero_id',
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
