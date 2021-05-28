<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use DB;

class DescuentoProntoPago extends Model
{
    protected $table = 'compras_descuentos_pronto_pago';
    
    protected $fillable = ['descripcion', 'contab_cuenta_id', 'estado'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Descripción', 'Cuenta de ingresos', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return DescuentoProntoPago::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'compras_descuentos_pronto_pago.contab_cuenta_id')
                                    ->select(
                                            'compras_descuentos_pronto_pago.descripcion AS campo1',
                                            DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo2'),
                                            'compras_descuentos_pronto_pago.estado AS campo3',
                                            'compras_descuentos_pronto_pago.id AS campo4')
                                    ->paginate($nro_registros);
    }
    
    public static function sqlString($search)
    {
        $string = DescuentoProntoPago::select('compras_descuentos_pronto_pago.descripcion AS campo1',
            'compras_descuentos_pronto_pago.contab_cuenta_id AS campo2', 'compras_descuentos_pronto_pago.estado AS campo3', 'compras_descuentos_pronto_pago.id AS campo4')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE DESCUENTOS POR PRONTO PAGO";
    }

    public static function opciones_campo_select()
    {
        $opciones = DescuentoProntoPago::where('compras_descuentos_pronto_pago.estado','Activo')
                    ->select('compras_descuentos_pronto_pago.id','compras_descuentos_pronto_pago.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
