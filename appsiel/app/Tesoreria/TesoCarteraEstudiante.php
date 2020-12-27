<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

use App\Tesoreria\TesoRecaudosLibreta;

// PLAN DE PAGOS del estudiante

class TesoCarteraEstudiante extends Model
{
    // NOTA: el campo "concepto" debe cambiar por uno tipo ID, que es el que se usa en la facturas.
    protected $fillable = ['id_libreta','id_estudiante','concepto',
    						'valor_cartera','valor_pagado','saldo_pendiente','fecha_vencimiento','estado'];

    public function libreta()
    {
        return $this->belongsTo( TesoLibretasPago::class, 'id_libreta');
    }

    public function estudiante()
    {
        return $this->belongsTo( 'App\Matriculas\Estudiante', 'id_estudiante');
    }

    public function facturas_estudiantes()
    {
        return $this->hasMany( 'App\Matriculas\FacturaAuxEstudiante', 'cartera_estudiante_id');
    }

    public function recaudo_tesoreria()
    {
        return TesoRecaudosLibreta::where( 'id_cartera', $this->id )->get()->first()->recaudo_tesoreria();
    }

    public static function get_cartera_estudiantes_curso( $curso_id, $fecha_vencimiento, $concepto)
    {

    	$cadena="%-".$fecha_vencimiento."-%";

        if ( $curso_id == '') 
        {
            $curso_id = '%%';
        }

    	return TesoCarteraEstudiante::leftJoin('sga_estudiantes','sga_estudiantes.id','=','teso_cartera_estudiantes.id_estudiante')
                        ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                        ->leftJoin('teso_libretas_pagos','teso_libretas_pagos.id_estudiante','=','sga_estudiantes.id')
                        ->leftJoin('sga_matriculas','sga_matriculas.id','=','teso_libretas_pagos.matricula_id')
                        ->leftJoin('sga_cursos','sga_cursos.id','=','sga_matriculas.curso_id')
                        ->where('sga_matriculas.curso_id', 'LIKE', $curso_id)
                		->where('teso_cartera_estudiantes.fecha_vencimiento','LIKE', $cadena)
                        ->where('teso_cartera_estudiantes.inv_producto_id', '=', $concepto)
                        ->where('teso_cartera_estudiantes.estado','=','Vencida')
                        ->where('teso_cartera_estudiantes.saldo_pendiente','<>',0)
                        ->select(
                        			DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo' ),
                        			'core_terceros.numero_identificacion AS doc_identidad',
                        			'core_terceros.apellido1',
                        			'teso_cartera_estudiantes.valor_cartera',
                        			'teso_cartera_estudiantes.valor_pagado',
                        			'sga_matriculas.codigo AS codigo_matricula',
                        			'sga_cursos.descripcion AS nom_curso',
                        			'sga_cursos.codigo AS codigo_curso')
                        ->orderBy('sga_cursos.codigo','ASC')
                        ->orderBy('core_terceros.apellido1','ASC')
                        ->get();
    }
}
