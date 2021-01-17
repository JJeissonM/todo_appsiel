<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class DescuentoPpEncabezado extends Model
{
    protected $table = 'vtas_descuentos_pp_encabezados';
	protected $fillable = ['descripcion', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros)
    {
        $registros = DescuentoPpEncabezado::select('vtas_descuentos_pp_encabezados.descripcion AS campo1', 'vtas_descuentos_pp_encabezados.estado AS campo2', 'vtas_descuentos_pp_encabezados.id AS campo3')
            ->orderBy('vtas_descuentos_pp_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

	public static function opciones_campo_select()
    {
        $opciones = DescuentoPpEncabezado::where('vtas_descuentos_pp_encabezados.estado','Activo')
                    ->select('vtas_descuentos_pp_encabezados.id','vtas_descuentos_pp_encabezados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
