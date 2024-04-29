<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;
 
use App\User;
use App\Matriculas\PeriodoLectivo;

use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Asignatura;
use App\Matriculas\Curso;

class AsignacionProfesor extends Model
{
    protected $table = 'sga_asignaciones_profesores';

    protected $fillable = ['periodo_lectivo_id', 'id_user', 'curso_id', 'id_asignatura'];

    public function profesor()
    {
        return $this->belongsTo( User::class, 'id_user' );
    }

    public function curso()
    {
        return $this->belongsTo( Curso::class, 'curso_id' );
    }

    public function asignatura()
    {
        return $this->belongsTo( Asignatura::class, 'id_asignatura' );
    }

    /*
        Las asignaciones para un profesor
    */
    public static function get_asignaturas_x_curso( $user_id, $periodo_lectivo_id = null )
    { 
        $array_wheres = [ ];

        // Si se envia nulo el ID del usuario, no lo tienen en cuenta para filtrar
        if ( !is_null( $user_id) )
        {
            $array_wheres = array_merge( $array_wheres, [ 'sga_asignaciones_profesores.id_user' => $user_id ] );
        }


        if ( $periodo_lectivo_id == null)
        {
            $array_wheres = array_merge( $array_wheres, [ 'sga_asignaciones_profesores.periodo_lectivo_id' => PeriodoLectivo::get_actual()->id ] );
        }else{
            // Si la variable $periodo_lectivo_id tiene el valor 'todos', no se filtar por periodo 
            if ( $periodo_lectivo_id != 'todos' )
            {
                $array_wheres = array_merge( $array_wheres, [ 'sga_asignaciones_profesores.periodo_lectivo_id' => $periodo_lectivo_id ] );
            }            
        }

        return AsignacionProfesor::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_asignaciones_profesores.periodo_lectivo_id')
                            ->leftJoin('sga_cursos','sga_cursos.id','=','sga_asignaciones_profesores.curso_id')
                            ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_asignaciones_profesores.id_asignatura')
                            ->where( $array_wheres )
                            ->select(
                                        'sga_asignaciones_profesores.id',
                                        'sga_asignaciones_profesores.periodo_lectivo_id',
                                        'sga_asignaciones_profesores.id_user',
                                        'sga_asignaciones_profesores.curso_id',
                                        'sga_cursos.descripcion AS Curso',
                                        'sga_asignaciones_profesores.id_asignatura',
                                        'sga_asignaturas.descripcion AS Asignatura')
                            ->orderBy('Curso')
                            ->orderBy('Asignatura')
                            ->get();
    }

    public static function get_asignacion_x_user_curso_asignatura( $user_id, $curso_id, $asignatura_id, $periodo_lectivo_id = null )
    {
        if ( $periodo_lectivo_id == null)
        {
            $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;
        }

        return AsignacionProfesor::where('periodo_lectivo_id',$periodo_lectivo_id)
                                ->where('id_user',$user_id)
                                ->where('curso_id',$curso_id)
                                ->where('id_asignatura',$asignatura_id)
                                ->get()
                                ->first();
    }

    public static function get_asignaturas_por_usuario( int $user_id, int $curso_id, int $periodo_lectivo_id )
    {
        return AsignacionProfesor::where([
                                ['periodo_lectivo_id', '=', $periodo_lectivo_id],
                                ['id_user', '=', $user_id],
                                ['curso_id', '=', $curso_id]
                            ])
                                ->get();
    }

    public static function get_user_segun_curso_asignatura( $curso_id, $asignatura_id, $periodo_lectivo_id = null )
    {
        if ( $periodo_lectivo_id == null)
        {
            $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;
        }

        $user_id = AsignacionProfesor::where('periodo_lectivo_id',$periodo_lectivo_id)
                                    ->where('curso_id',$curso_id)
                                    ->where('id_asignatura',$asignatura_id)
                                    ->value('id_user');

        return User::find($user_id);
    }

    public static function get_profesor_de_la_asignatura( $curso_id, $asignatura_id, $periodo_lectivo_id )
    {
        return AsignacionProfesor::where('periodo_lectivo_id',$periodo_lectivo_id)
                                    ->where('curso_id',$curso_id)
                                    ->where('id_asignatura',$asignatura_id)
                                    ->get()
                                    ->first();
    }

    /*
        Este método devuelve una collection de las asignaturas que aún tienen profesor(user) asignado en el periodo lectivo
    */
    public static function get_asignaturas_pendientes( $periodo_lectivo_id, $curso_id )
    {
        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso($curso_id, null, $periodo_lectivo_id );

        /*  
            Esto se debería hacer en una sola consulta SQL y no usar el foreach()
        */

        foreach ($asignaturas as $key => $value )
        {
            // Se revisa que ningún usuario tenga la asignatura en el curso y periodo lectivo
            $user = AsignacionProfesor::get_user_segun_curso_asignatura( $curso_id, $value->id, $periodo_lectivo_id );

            // Si la asignatura ya está asignada, se elimina de la colletion
            if ( !is_null( $user ) )
            {
                $asignaturas->forget( $key );
            }
        }

        return $asignaturas;

    }

    public static function get_opciones_select_asignaturas_pendientes( $periodo_lectivo_id, $curso_id )
    {
        $opciones = AsignacionProfesor::get_asignaturas_pendientes( $periodo_lectivo_id, $curso_id );
        
        $select = '<option value="">Seleccionar... </option>';
        foreach ($opciones as $opcion){
            $select.='<option value="'.$opcion->id.'">' . $opcion->descripcion . ' (' . $opcion->asignatura->area->descripcion . ') </option>';
        }

        return $select;
    }
}
