<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomEntidad extends Model
{
    protected $table = 'nom_entidades';

    protected $fillable = ['core_tercero_id', 'descripcion', 'codigo_nacional', 'tipo_entidad', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'NIT', 'Razón Social', 'Nombre Entidad', 'Código nacional', 'Tipo Entidad', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        return NomEntidad::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_entidades.core_tercero_id')
            ->select(
                'core_terceros.numero_identificacion AS campo1',
                'core_terceros.descripcion AS campo2',
                'nom_entidades.descripcion AS campo3',
                'nom_entidades.codigo_nacional AS campo4',
                'nom_entidades.tipo_entidad AS campo5',
                'nom_entidades.estado AS campo6',
                'nom_entidades.id AS campo7'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_entidades.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_entidades.codigo_nacional", "LIKE", "%$search%")
            ->orWhere("nom_entidades.tipo_entidad", "LIKE", "%$search%")
            ->orWhere("nom_entidades.estado", "LIKE", "%$search%")
            ->orderBy('nom_entidades.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = NomEntidad::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_entidades.core_tercero_id')
            ->select(
                'core_terceros.numero_identificacion AS NIT',
                'core_terceros.descripcion AS RAZÓN_SOCIAL',
                'nom_entidades.descripcion AS NOMBRE_ENTIDAD',
                'nom_entidades.codigo_nacional AS CÓDIGO_NACIONAL',
                'nom_entidades.tipo_entidad AS TIPO_ENTIDAD',
                'nom_entidades.estado AS ESTADO',
                'nom_entidades.id AS ID',
                'core_terceros.id AS TERCERO_ID'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_entidades.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_entidades.codigo_nacional", "LIKE", "%$search%")
            ->orWhere("nom_entidades.tipo_entidad", "LIKE", "%$search%")
            ->orWhere("nom_entidades.estado", "LIKE", "%$search%")
            ->orderBy('nom_entidades.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ENTIDADES";
    }
    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public static function opciones_campo_select()
    {
        $opciones = NomEntidad::where('estado', 'Activo')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
