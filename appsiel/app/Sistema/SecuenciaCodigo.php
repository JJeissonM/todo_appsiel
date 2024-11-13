<?php

namespace App\Sistema;

use Illuminate\Database\Eloquent\Model;

use App\Matriculas\Grado;
use Auth;

// Para gestionar los concecutivos (código) de algunos módulos
class SecuenciaCodigo extends Model
{
    protected $table = 'sys_secuencias_codigos';

    protected $fillable = ['id_colegio', 'modulo', 'consecutivo', 'anio', 'estructura_secuencia', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Módulo', 'Consecutivo actual', 'Año (AA)', 'Estructura secuencia', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = SecuenciaCodigo::select(
            'sys_secuencias_codigos.modulo AS campo1',
            'sys_secuencias_codigos.consecutivo AS campo2',
            'sys_secuencias_codigos.anio AS campo3',
            'sys_secuencias_codigos.estructura_secuencia AS campo4',
            'sys_secuencias_codigos.estado AS campo5',
            'sys_secuencias_codigos.id AS campo6'
        )
            ->where("sys_secuencias_codigos.modulo", "LIKE", "%$search%")
            ->orWhere("sys_secuencias_codigos.consecutivo", "LIKE", "%$search%")
            ->orWhere("sys_secuencias_codigos.anio", "LIKE", "%$search%")
            ->orWhere("sys_secuencias_codigos.estructura_secuencia", "LIKE", "%$search%")
            ->orWhere("sys_secuencias_codigos.estado", "LIKE", "%$search%")
            ->orderBy('sys_secuencias_codigos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = SecuenciaCodigo::select(
            'sys_secuencias_codigos.modulo AS MÓDULO',
            'sys_secuencias_codigos.consecutivo AS CONSECUTIVO_ACTUAL',
            'sys_secuencias_codigos.anio AS AÑO_(AA)',
            'sys_secuencias_codigos.estructura_secuencia AS ESTRUCUTURA_SECUENCIA',
            'sys_secuencias_codigos.estado AS ESTADO'
        )
            ->where("sys_secuencias_codigos.modulo", "LIKE", "%$search%")
            ->orWhere("sys_secuencias_codigos.consecutivo", "LIKE", "%$search%")
            ->orWhere("sys_secuencias_codigos.anio", "LIKE", "%$search%")
            ->orWhere("sys_secuencias_codigos.estructura_secuencia", "LIKE", "%$search%")
            ->orWhere("sys_secuencias_codigos.estado", "LIKE", "%$search%")
            ->orderBy('sys_secuencias_codigos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE SECUENCIA DE CODIGOS";
    }

    public static function get_codigo($modulo, $otros_datos = null)
    {
        // Generar el código
        $largo_consecutivo = (int)config('matriculas.largo_consecutivo_codigo_matricula');
        $secuencia = SecuenciaCodigo::where(['modulo' => $modulo, 'estado' => 'Activo'])->get()->first();
        $consecutivo = $secuencia->consecutivo + 1;
        $largo = strlen($consecutivo);

        switch ($secuencia->estructura_secuencia) {
            case '(anio)-(consecutivo)':
                $codigo = $secuencia->anio . '-' . str_repeat('0', $largo_consecutivo - $largo) . $consecutivo;
                break;

            case '(consecutivo)':
                $codigo = $consecutivo;
                break;

            case '(anio)(consecutivo)-(grado)':

                $grado = Grado::find($otros_datos->grado_id);
                $codigo = $secuencia->anio . str_repeat('0', $largo_consecutivo - $largo) . $consecutivo . '-' . $grado->codigo;
                break;

            default:
                $codigo = $consecutivo;
                break;
        }

        return $codigo;
    }

    public static function incrementar_consecutivo($modulo)
    {
        SecuenciaCodigo::where(['modulo' => $modulo, 'estado' => 'Activo'])->increment('consecutivo');
    }
}
