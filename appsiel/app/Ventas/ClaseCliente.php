<?php

namespace App\Ventas;

use DB;

use Illuminate\Database\Eloquent\Model;

class ClaseCliente extends Model
{
    protected $table = 'vtas_clases_clientes';

    protected $fillable = ['descripcion', 'cta_x_cobrar_id', 'cta_anticipo_id', 'clase_padre_id', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tercero', 'Cta x cobrar default', 'Cta anticipo default', 'Clase padre', 'Estado'];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = ClaseCliente::leftJoin('contab_cuentas as cta_x_cobrar', 'cta_x_cobrar.id', '=', 'vtas_clases_clientes.cta_x_cobrar_id')
            ->leftJoin('contab_cuentas as cta_anticipo', 'cta_anticipo.id', '=', 'vtas_clases_clientes.cta_anticipo_id')
            ->select(
                'vtas_clases_clientes.descripcion AS campo1',
                'cta_x_cobrar.descripcion AS campo2',
                'cta_anticipo.descripcion AS campo3',
                'vtas_clases_clientes.clase_padre_id AS campo4',
                'vtas_clases_clientes.estado AS campo5',
                'vtas_clases_clientes.id AS campo6'
            )
            ->where("vtas_clases_clientes.descripcion", "LIKE", "%$search%")
            ->orWhere("cta_x_cobrar.descripcion", "LIKE", "%$search%")
            ->orWhere("cta_anticipo.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_clases_clientes.clase_padre_id", "LIKE", "%$search%")
            ->orWhere("vtas_clases_clientes.estado", "LIKE", "%$search%")
            ->orderBy('vtas_clases_clientes.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = ClaseCliente::leftJoin('contab_cuentas as cta_x_cobrar', 'cta_x_cobrar.id', '=', 'vtas_clases_clientes.cta_x_cobrar_id')
            ->leftJoin('contab_cuentas as cta_anticipo', 'cta_anticipo.id', '=', 'vtas_clases_clientes.cta_anticipo_id')
            ->select(
                'vtas_clases_clientes.descripcion AS TERCERO',
                'cta_x_cobrar.descripcion AS CTA_X_COBRAR_DEFAULT',
                'cta_anticipo.descripcion AS CTA_ANTICIPO_DEFAULT',
                'vtas_clases_clientes.clase_padre_id AS CLASE_PADRE',
                'vtas_clases_clientes.estado AS ESTADO'
            )
            ->where("vtas_clases_clientes.descripcion", "LIKE", "%$search%")
            ->orWhere("cta_x_cobrar.descripcion", "LIKE", "%$search%")
            ->orWhere("cta_anticipo.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_clases_clientes.clase_padre_id", "LIKE", "%$search%")
            ->orWhere("vtas_clases_clientes.estado", "LIKE", "%$search%")
            ->orderBy('vtas_clases_clientes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CLASES DE CLIENTES";
    }

    public static function opciones_campo_select()
    {
        $opciones = ClaseCliente::where('vtas_clases_clientes.estado', 'Activo')
            ->select('vtas_clases_clientes.id', 'vtas_clases_clientes.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"vtas_clientes",
                                    "llave_foranea":"clase_cliente_id",
                                    "mensaje":"La clase está asociada a un Cliente."
                                },
                            "1":{
                                    "tabla":"vtas_movimientos",
                                    "llave_foranea":"clase_cliente_id",
                                    "mensaje":"La clase de cliente está en Movimientos de Ventas."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
