<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;

use App\Cuestionarios\Pregunta;
use App\Calificaciones\CursoTieneAsignatura;

use App\Matriculas\PeriodoLectivo;
use App\Calificaciones\Periodo;

class ActividadEscolar extends Model
{
    protected $table = 'sga_actividades_escolares'; 

    protected $fillable = ['descripcion','tematica','instrucciones','tipo_recurso','url_recurso','cuestionario_id','fecha_entrega','fecha_desde','fecha_hasta','periodo_id','curso_id','asignatura_id','estado'];

    public $encabezado_tabla = ['Título','Temática','Fecha de entrega','Curso','Asignatura','Estado','Acción'];
    
    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/calificaciones/actividades_escolares/actividades.js';

    public static function consultar_registros()
    {
        // Filtro año lectivo actual
        $periodo_lectivo_actual = PeriodoLectivo::get_actual();

        $periodos = Periodo::where( 'periodo_lectivo_id', $periodo_lectivo_actual->id )->select('id')->get()->pluck('id');

        // Filtros por Perfil de usuario
        $user = Auth::user();
        $array_wheres = [ 
                            ['sga_actividades_escolares.id' ,'>', 0]
                        ];
        
        if ( $user->hasRole('SuperAdmin') || $user->hasRole('Admin Colegio') || $user->hasRole('Colegio - Vicerrector') || $user->hasRole('Administrador') ) 
        {
            //$array_wheres = array_merge($array_wheres, [['core_acl.user_id', '>', 0]]);          
        }else{
            $array_wheres = array_merge($array_wheres, ['core_acl.user_id' => $user->id]);
        }

        return ActividadEscolar::leftJoin('core_acl','core_acl.recurso_id','=','sga_actividades_escolares.id')
                                ->leftJoin('sga_cursos','sga_cursos.id','=','sga_actividades_escolares.curso_id')
                                ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_actividades_escolares.asignatura_id')
                                ->where( $array_wheres )
                                ->whereIn( 'sga_actividades_escolares.periodo_id', $periodos )
                                ->select(
                                            'sga_actividades_escolares.descripcion AS campo1',
                                            'sga_actividades_escolares.tematica AS campo2',
                                            'sga_actividades_escolares.fecha_entrega AS campo3',
                                            'sga_cursos.descripcion AS campo4',
                                            'sga_asignaturas.descripcion AS campo5',
                                            'sga_actividades_escolares.estado AS campo6',
                                            'sga_actividades_escolares.id AS campo7')
                                ->get()
                                ->toArray();
    }


    public function estudiantes()
    {
        return $this->belongsToMany('App\Matriculas\Estudiante','estudiante_tiene_actividades_escolares','actividad_escolar_id','estudiante_id');
    }


    // PADRE = CURSO, HIJO = ASIGNATURAS
    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = CursoTieneAsignatura::asignaturas_del_curso( $id_select_padre, null, null, null );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {
                            
            $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }


    public static function get_actividades_periodo_lectivo_actual( $curso_id, $asignatura_id )
    {
        $periodo_lectivo_actual = PeriodoLectivo::get_actual();

        $periodos = Periodo::where( 'periodo_lectivo_id', $periodo_lectivo_actual->id )->select('id')->get()->pluck('id');

        return ActividadEscolar::leftJoin('sga_periodos','sga_periodos.id','=','sga_actividades_escolares.periodo_id')
                                        ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_actividades_escolares.asignatura_id')
                                        ->whereIn( 'sga_actividades_escolares.periodo_id', $periodos )
                                        ->where('sga_actividades_escolares.estado','Activo')
                                        ->where('sga_actividades_escolares.curso_id', $curso_id)
                                        ->where('sga_asignaturas.id', $asignatura_id)
                                        ->select(
                                                'sga_actividades_escolares.id',
                                                'sga_asignaturas.descripcion AS asignatura_descripcion',
                                                'sga_periodos.descripcion AS periodo_descripcion',
                                                'sga_actividades_escolares.descripcion',
                                                'sga_actividades_escolares.tematica',
                                                'sga_actividades_escolares.fecha_entrega')
                                        ->get();

    }

}