<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;

use App\Cuestionarios\Pregunta;
use App\Calificaciones\CursoTieneAsignatura;

class ActividadEscolar extends Model
{
    protected $table = 'sga_actividades_escolares'; 

    protected $fillable = ['descripcion','tematica','instrucciones','tipo_recurso','url_recurso','cuestionario_id','fecha_entrega','fecha_desde','fecha_hasta','periodo_id','curso_id','asignatura_id','estado'];

    public $encabezado_tabla = ['Título','Temática','Fecha de entrega','Curso','Asignatura','Estado','Acción'];
    
    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/calificaciones/actividades_escolares/actividades.js';

    public static function consultar_registros()
    {
        return ActividadEscolar::leftJoin('core_acl','core_acl.recurso_id','=','sga_actividades_escolares.id')
                                ->where('core_acl.user_id', Auth::user()->id)
                                ->leftJoin('sga_cursos','sga_cursos.id','=','sga_actividades_escolares.curso_id')
                                ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_actividades_escolares.asignatura_id')
                                ->where('core_acl.user_id', Auth::user()->id)
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
}