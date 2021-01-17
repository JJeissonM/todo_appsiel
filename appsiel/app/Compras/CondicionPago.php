<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class CondicionPago extends Model
{
    protected $table = 'compras_condiciones_pago';
	protected $fillable = ['descripcion', 'dias_plazo', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Condición de pago', 'Días de plazo', 'Estado'];
    public static function consultar_registros($nro_registros, $search)
    {
        return CondicionPagoProv::select(
            'compras_condiciones_pago.descripcion AS campo1',
            'compras_condiciones_pago.dias_plazo AS campo2',
            'compras_condiciones_pago.estado AS campo3',
            'compras_condiciones_pago.id AS campo4'
        )
            ->where("compras_condiciones_pago.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_condiciones_pago.dias_plazo", "LIKE", "%$search%")
            ->orWhere("compras_condiciones_pago.estado", "LIKE", "%$search%")
            ->orderBy('compras_condiciones_pago.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = CondicionPagoProv::select(
            'compras_condiciones_pago.descripcion AS CONDICIÓN_DE_PAGO',
            'compras_condiciones_pago.dias_plazo AS DÍAS_DE_PLAZO',
            'compras_condiciones_pago.estado AS ESTADO'
        )
            ->where("compras_condiciones_pago.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_condiciones_pago.dias_plazo", "LIKE", "%$search%")
            ->orWhere("compras_condiciones_pago.estado", "LIKE", "%$search%")
            ->orderBy('compras_condiciones_pago.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ";
    }

    public static function opciones_campo_select()
    {
        $opciones = CondicionPagoProv::where('compras_condiciones_pago.estado','Activo')
                    ->select('compras_condiciones_pago.id','compras_condiciones_pago.descripcion')
                    ->get();

        //$vec['']='';
    	$vec = [];
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
