<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;

class DescuentoPpEncabezado extends Model
{
    protected $table = 'vtas_descuentos_pp_encabezados';
    
    protected $fillable = [ 'descripcion', 'contab_cuenta_id', 'estado' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Cuenta de gastos', 'Estado'];
    
    public static function consultar_registros($nro_registros, $search)
    {
        $registros = DescuentoPpEncabezado::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'vtas_descuentos_pp_encabezados.contab_cuenta_id')
                                    ->select(
                                        'vtas_descuentos_pp_encabezados.descripcion AS campo1',
                                        DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo2'),
                                        'vtas_descuentos_pp_encabezados.estado AS campo3',
                                        'vtas_descuentos_pp_encabezados.id AS campo4'
                                        )
                                    ->where("vtas_descuentos_pp_encabezados.descripcion", "LIKE", "%$search%")
                                    ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
                                    ->orWhere("vtas_descuentos_pp_encabezados.estado", "LIKE", "%$search%")
                                    ->orderBy('vtas_descuentos_pp_encabezados.created_at', 'DESC')
                                    ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = DescuentoPpEncabezado::select(
            'vtas_descuentos_pp_encabezados.descripcion AS DESCRIPCIÓN',
            'vtas_descuentos_pp_encabezados.estado AS ESTADO'
        )
            ->where("vtas_descuentos_pp_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_descuentos_pp_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_descuentos_pp_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ENCABEZADO DESCUENTO PP";
    }

    public static function opciones_campo_select()
    {
        $opciones = DescuentoPpEncabezado::where('vtas_descuentos_pp_encabezados.estado', 'Activo')
            ->select('vtas_descuentos_pp_encabezados.id', 'vtas_descuentos_pp_encabezados.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
