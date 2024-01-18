<?php 

namespace App\Matriculas\Services;

use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;

use Illuminate\Support\Facades\DB;

class EstudiantesService
{
    public static function get_nombre_completo($id, $modo_ordenamiento = 1)
    {
        switch ($modo_ordenamiento) {
            case '1': // Apellidos_Nombre
                $select_raw = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1';
                break;

            case '2': // Nombre_Apellidos
                $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
                break;

            default:
                # code...
                break;
        }

        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        }

        return Estudiante::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                            ->where('sga_estudiantes.id', $id)
                            ->select(DB::raw($select_raw))
                            ->value('campo1');
    }

    public function historial_matriculas($estudiante_id)
    {
        return Matricula::where('id_estudiante', $estudiante_id)->get();
    } 
}