<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

class NomCargo extends Model
{
    //protected $table = 'nom_cargos';
    protected $fillable = ['descripcion', 'estado', 'cargo_padre_id', 'rango_salarial_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = NomCargo::select(
            'nom_cargos.descripcion AS campo1',
            'nom_cargos.estado AS campo2',
            'nom_cargos.id AS campo3'
        )
            ->where("nom_cargos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_cargos.estado", "LIKE", "%$search%")
            ->orderBy('nom_cargos.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = NomCargo::select(
            'nom_cargos.descripcion AS DESCRIPCIÓN',
            'nom_cargos.estado AS ESTADO'
        )
            ->where("nom_cargos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_cargos.estado", "LIKE", "%$search%")
            ->orderBy('nom_cargos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CARGOS";
    }

    public static function opciones_campo_select()
    {
        $opciones = NomCargo::where('estado', 'Activo')->get();

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
                                    "tabla":"nom_contratos",
                                    "llave_foranea":"cargo_id",
                                    "mensaje":"Está asociado al Contrato de un empleado."
                                },
                            "1":{
                                    "tabla":"nom_cargos",
                                    "llave_foranea":"cargo_padre_id",
                                    "mensaje":"Está asociado como Cargo padre de otro Cargo."
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
