<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use DB;

class Area extends Model
{
    protected $table = 'sga_areas';    

    protected $fillable = ['colegio_id', 'orden_listados', 'descripcion', 'abreviatura', 'estado'];

    public $encabezado_tabla = ['Orden listados','Descripción', 'Abreviatura','Estado','Acción'];

    public static function consultar_registros()
    {
        $registros = Area::select('sga_areas.orden_listados AS campo1','sga_areas.descripcion AS campo2', 'sga_areas.abreviatura AS campo3','sga_areas.estado AS campo4','sga_areas.id AS campo5')
            ->get()
            ->toArray();

        return $registros;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"sga_asignaturas",
                                    "llave_foranea":"area_id",
                                    "mensaje":"Tiene asignaturas relacionadas."
                                }
                        }';

        $tablas = json_decode( $tablas_relacionadas );
        //$cantidad = count($tablas);
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                //dd([ $una_tabla->tabla, $una_tabla->llave_foranea, $id, $registro, $una_tabla->mensaje ] );
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    } 
}
