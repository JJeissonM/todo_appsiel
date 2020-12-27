<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;

class NovedadesObservador extends Model
{
    protected $table = 'sga_novedades_observador';

    protected $fillable = ['id_estudiante', 'id_periodo', 'fecha_novedad', 'tipo_novedad', 'descripcion', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Periodo', 'Fecha novedad', 'Tipo novedad', 'Descripción', 'Creado por'];

    public static function consultar_registros($nro_registros)
    {
        return NovedadesObservador::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_novedades_observador.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_novedades_observador.id_periodo')
            ->leftJoin('users', 'users.email', '=', 'sga_novedades_observador.creado_por')
            ->leftJoin('sga_tipos_novedades', 'sga_tipos_novedades.id', '=', 'sga_novedades_observador.tipo_novedad')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                'sga_periodos.descripcion AS campo2',
                'sga_novedades_observador.fecha_novedad AS campo3',
                'sga_tipos_novedades.descripcion AS campo4',
                'sga_novedades_observador.descripcion AS campo5',
                'users.name AS campo6',
                'sga_novedades_observador.id AS campo7'
            )->orderBy('sga_novedades_observador.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function get_novedades_un_estudiante($estudiante_id)
    {
        return NovedadesObservador::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_novedades_observador.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_novedades_observador.id_periodo')
            ->leftJoin('sga_tipos_novedades', 'sga_tipos_novedades.id', '=', 'sga_novedades_observador.tipo_novedad')
            ->leftJoin('users', 'users.email', '=', 'sga_novedades_observador.creado_por')
            ->where('sga_novedades_observador.id_estudiante', $estudiante_id)
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                'sga_periodos.descripcion AS campo2',
                'sga_novedades_observador.fecha_novedad AS campo3',
                'sga_tipos_novedades.descripcion AS campo4',
                'sga_novedades_observador.descripcion AS campo5',
                'users.name AS campo6',
                'sga_novedades_observador.id AS campo7'
            )
            ->get()
            ->toArray();
    }
}
