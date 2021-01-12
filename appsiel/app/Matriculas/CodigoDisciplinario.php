<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class CodigoDisciplinario extends Model
{
    protected $table = 'sga_codigos_disciplinarios';

    protected $fillable = ['colegio_id', 'descripcion', 'tipo_codigo', 'estado'];

    // Calificacion: { positiva | negativa }
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Calificación', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = CodigoDisciplinario::select('sga_codigos_disciplinarios.id AS campo1', 'sga_codigos_disciplinarios.descripcion AS campo2', 'sga_codigos_disciplinarios.tipo_codigo AS campo3', 'sga_codigos_disciplinarios.estado AS campo4', 'sga_codigos_disciplinarios.id AS campo5')
            ->where("sga_codigos_disciplinarios.id", "LIKE", "%$search%")
            ->orWhere("sga_codigos_disciplinarios.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_codigos_disciplinarios.tipo_codigo", "LIKE", "%$search%")
            ->orWhere("sga_codigos_disciplinarios.estado", "LIKE", "%$search%")
            ->orderBy('sga_codigos_disciplinarios.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = CodigoDisciplinario::select('sga_codigos_disciplinarios.descripcion AS DESCRIPCIÓN', 'sga_codigos_disciplinarios.tipo_codigo AS CALIFICACIÓN', 'sga_codigos_disciplinarios.estado AS ESTADO')
            ->where("sga_codigos_disciplinarios.id", "LIKE", "%$search%")
            ->orWhere("sga_codigos_disciplinarios.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_codigos_disciplinarios.tipo_codigo", "LIKE", "%$search%")
            ->orWhere("sga_codigos_disciplinarios.estado", "LIKE", "%$search%")
            ->orderBy('sga_codigos_disciplinarios.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CODIGOS DISCIPLINARIO";
    }
}
