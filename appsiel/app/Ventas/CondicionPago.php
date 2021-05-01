<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Schema;

class CondicionPago extends Model
{
    protected $table = 'vtas_condiciones_pago';
    protected $fillable = ['descripcion', 'dias_plazo', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tercero', 'Días de plazo', 'Estado'];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return CondicionPago::select(
                                        'vtas_condiciones_pago.descripcion AS campo1',
                                        'vtas_condiciones_pago.dias_plazo AS campo2',
                                        'vtas_condiciones_pago.estado AS campo3',
                                        'vtas_condiciones_pago.id AS campo4'
                                    )
                            ->where("vtas_condiciones_pago.descripcion", "LIKE", "%$search%")
                            ->orWhere("vtas_condiciones_pago.dias_plazo", "LIKE", "%$search%")
                            ->orWhere("vtas_condiciones_pago.estado", "LIKE", "%$search%")
                            ->orderBy('vtas_condiciones_pago.dias_plazo')
                            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = CondicionPago::select(
            'vtas_condiciones_pago.descripcion AS TERCERO',
            'vtas_condiciones_pago.dias_plazo AS DÍAS_DE_PLAZO',
            'vtas_condiciones_pago.estado AS ESTADO'
        )
            ->where("vtas_condiciones_pago.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_condiciones_pago.dias_plazo", "LIKE", "%$search%")
            ->orWhere("vtas_condiciones_pago.estado", "LIKE", "%$search%")
            ->orderBy('vtas_condiciones_pago.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONDICION DE PAGO";
    }

    public static function opciones_campo_select()
    {
        $opciones = CondicionPago::where('vtas_condiciones_pago.estado', 'Activo')
            ->select('vtas_condiciones_pago.id', 'vtas_condiciones_pago.descripcion')
            ->get();

        //$vec['']='';
        $vec = [];
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
                                            "llave_foranea":"condicion_pago_id",
                                            "mensaje":"Condición de pago está asociada a un Cliente."
                                        }
                                }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            if ( !Schema::hasTable( $una_tabla->tabla ) )
            {
                continue;
            }
            
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
