<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use App\Compras\ClaseProveedor;

class Proveedor extends Model
{
    protected $table = 'compras_proveedores';
	
	protected $fillable = ['core_tercero_id', 'clase_proveedor_id', 'inv_bodega_id', 'liquida_impuestos', 'condicion_pago_id', 'estado'];

	public $encabezado_tabla = ['Identificación', 'Tercero', 'Dirección', 'Teléfono', 'Clase de proveedor', 'Liquida impuestos', 'Condición de pago', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = Proveedor::leftJoin('core_terceros','core_terceros.id','=','compras_proveedores.core_tercero_id')->leftJoin('compras_clases_proveedores','compras_clases_proveedores.id','=','compras_proveedores.clase_proveedor_id')->leftJoin('compras_condiciones_pago','compras_condiciones_pago.id','=','compras_proveedores.condicion_pago_id')
            ->select('core_terceros.numero_identificacion AS campo1', 'core_terceros.descripcion AS campo2', 'core_terceros.direccion1 AS campo3', 'core_terceros.telefono1 AS campo4', 'compras_clases_proveedores.descripcion AS campo5', 'compras_proveedores.liquida_impuestos AS campo6', 'compras_condiciones_pago.descripcion AS campo7', 'compras_proveedores.estado AS campo8', 'compras_proveedores.id AS campo9')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = Proveedor::leftJoin('core_terceros','core_terceros.id','=','compras_proveedores.core_tercero_id')->where('compras_proveedores.estado','Activo')
                    ->select('compras_proveedores.id','core_terceros.descripcion')
                    ->orderby('core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_cuenta_por_pagar( $proveedor_id )
    {
        $clase_proveedor_id = Proveedor::where( 'id', $proveedor_id )->value( 'clase_proveedor_id' );

        $cta_x_pagar_id = ClaseProveedor::where( 'id', $clase_proveedor_id )->value( 'cta_x_pagar_id' );

        if ( is_null($cta_x_pagar_id) )
        {
            $cta_x_pagar_id = config('configuracion.cta_por_pagar_default');
        }

        return $cta_x_pagar_id;
    }
}
