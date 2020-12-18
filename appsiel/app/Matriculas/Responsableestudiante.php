<?php

namespace App\Matriculas;

use App\Core\Tercero;
use App\Matriculas\Tiporesponsable;
use App\Matriculas\Estudiante;
use Illuminate\Database\Eloquent\Model;

use DB;

class Responsableestudiante extends Model
{
    protected $table = 'sga_responsableestudiantes';

    protected $fillable = ['id', 'direccion_trabajo', 'telefono_trabajo', 'puesto_trabajo', 'empresa_labora', 'jefe_inmediato', 'telefono_jefe', 'descripcion_trabajador_independiente', 'ocupacion', 'tiporesponsable_id', 'estudiante_id', 'tercero_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['ID', 'Estudiante', 'Responsable', 'Tipo', 'Dir.', 'Teléfono', 'Ocupación', 'Acción'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function tiporesponsable()
    {
        return $this->belongsTo(Tiporesponsable::class);
    }

    public function tercero()
    {
        return $this->belongsTo(Tercero::class);
    }

    public static function consultar_registros()
    {       
        return Responsableestudiante::leftJoin('sga_estudiantes','sga_estudiantes.id','=','sga_responsableestudiantes.estudiante_id')
                                    ->leftJoin('core_terceros AS Estudiantes', 'Estudiantes.id', '=', 'sga_estudiantes.core_tercero_id')
                                    ->leftJoin('core_terceros AS Responsables', 'Responsables.id', '=', 'sga_responsableestudiantes.tercero_id')
                                    ->leftJoin('sga_tiporesponsables', 'sga_tiporesponsables.id', '=', 'sga_responsableestudiantes.tiporesponsable_id')
                                    ->select(
                                                'sga_responsableestudiantes.id AS campo1',
                                                DB::raw( 'CONCAT(Estudiantes.apellido1," ",Estudiantes.apellido2," ",Estudiantes.nombre1," ",Estudiantes.otros_nombres) AS campo2' ),
                                                DB::raw( 'CONCAT(Responsables.apellido1," ",Responsables.apellido2," ",Responsables.nombre1," ",Responsables.otros_nombres) AS campo3' ),
                                                'sga_tiporesponsables.descripcion AS campo4',
                                                'Responsables.direccion1 AS campo5',
                                                'Responsables.telefono1 AS campo6',
                                                'sga_responsableestudiantes.ocupacion AS campo7',
                                                'sga_responsableestudiantes.id AS campo8')
                                    ->get()
                                    ->toArray();
    }
}
