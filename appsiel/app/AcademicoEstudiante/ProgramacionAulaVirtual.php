<?php

namespace App\AcademicoEstudiante;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use Auth;

use App\Calificaciones\CursoTieneAsignatura;

use App\AcademicoDocente\AsignacionProfesor;

class ProgramacionAulaVirtual extends Model
{
    protected $table = 'sga_programacion_aula_virtual';

    /*
		tipo_evento: { clase_normal | descanso | otro }
    */
    protected $fillable = ['curso_id', 'descripcion', 'tipo_evento', 'dia_semana', 'hora_inicio', 'fecha', 'asignatura_id', 'guia_academica_id', 'actividad_escolar_id', 'enlace_reunion_virtual', 'creado_por', 'modificado_por', 'estado'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Curso', 'Tipo de evento', 'Descripción', 'Fecha', 'Día de la semana', 'Hora inicio', 'Asignatura', 'Creado por', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public function asignatura()
    {
    	return $this->belongsTo( 'App\Calificaciones\Asignatura', 'asignatura_id' );
    }

    public function curso()
    {
    	return $this->belongsTo( 'App\Matriculas\Curso', 'curso_id' );
    }

    public function guia_academica()
    {
    	return $this->belongsTo( 'App\AcademicoDocente\GuiaAcademica', 'guia_academica_id' );
    }

    public function actividad_escolar()
    {
    	return $this->belongsTo( 'App\Cuestionarios\ActividadEscolar', 'actividad_escolar_id' );
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/academico_docente/programacion_clases_aula_virtual.js';

    public static function consultar_registros($nro_registros, $search)
    {

        $user = Auth::user();

        if( $user->hasRole('SuperAdmin') || $user->hasRole('Admin Colegio') || $user->hasRole('Colegio - Vicerrector') || $user->hasRole('Administrador') )
        {
            $collection = ProgramacionAulaVirtual::leftJoin('sga_cursos','sga_cursos.id','=','sga_programacion_aula_virtual.curso_id')
                                            ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_programacion_aula_virtual.asignatura_id')
                                            ->select(
                                                    'sga_cursos.descripcion AS campo1',
                                                    'sga_programacion_aula_virtual.tipo_evento AS campo2',
                                                    'sga_programacion_aula_virtual.descripcion AS campo3',
                                                    'sga_programacion_aula_virtual.fecha AS campo4',
                                                    'sga_programacion_aula_virtual.dia_semana AS campo5',
                                                    'sga_programacion_aula_virtual.hora_inicio AS campo6',
                                                    'sga_asignaturas.descripcion AS campo7',
                                                    'sga_programacion_aula_virtual.creado_por AS campo8',
                                                    'sga_programacion_aula_virtual.estado AS campo9',
                                                    'sga_programacion_aula_virtual.id AS campo10')
                                            ->paginate($nro_registros);
        } else {

            $carga_academica_profesor = AsignacionProfesor::get_asignaturas_x_curso( $user->id );

            $vec_asignaturas = $carga_academica_profesor->pluck('id_asignatura')->toArray();

            $collection = ProgramacionAulaVirtual::leftJoin('sga_cursos','sga_cursos.id','=','sga_programacion_aula_virtual.curso_id')
                                            ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_programacion_aula_virtual.asignatura_id')
                                            ->whereIn('sga_programacion_aula_virtual.asignatura_id',$vec_asignaturas)
                                            ->select(
                                                    'sga_cursos.descripcion AS campo1',
                                                    'sga_programacion_aula_virtual.tipo_evento AS campo2',
                                                    'sga_programacion_aula_virtual.descripcion AS campo3',
                                                    'sga_programacion_aula_virtual.fecha AS campo4',
                                                    'sga_programacion_aula_virtual.dia_semana AS campo5',
                                                    'sga_programacion_aula_virtual.hora_inicio AS campo6',
                                                    'sga_asignaturas.descripcion AS campo7',
                                                    'sga_programacion_aula_virtual.creado_por AS campo8',
                                                    'sga_programacion_aula_virtual.estado AS campo9',
                                                    'sga_programacion_aula_virtual.id AS campo10')
                                            ->paginate($nro_registros);
        }

            

        /* $collection->items()[0]->forget('asignatura_id');
        dd( $collection->items() );
        if (count($collection) > 0)
        {
            foreach ($collection as $c)
            {
                $c->campo7 = $c->asignatura->descripcion;
            }

            $collection->forget('curso_id');
            $collection->forget('asignatura_id');
        }*/
        
        return $collection;
    }

    public static function sqlString($search)
    {
        $string = ProgramacionAulaVirtual::select('sga_programacion_aula_virtual.curso_id AS campo1', 'sga_programacion_aula_virtual.descripcion AS campo2', 'sga_programacion_aula_virtual.tipo_evento AS campo3', 'sga_programacion_aula_virtual.dia_semana AS campo4', 'sga_programacion_aula_virtual.hora_inicio AS campo5', 'sga_programacion_aula_virtual.fecha AS campo6', 'sga_programacion_aula_virtual.asignatura_id AS campo7', 'sga_programacion_aula_virtual.creado_por AS campo8', 'sga_programacion_aula_virtual.estado AS campo9', 'sga_programacion_aula_virtual.id AS campo10')
        								->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "PROGRAMACIONES AULA VIRTUAL";
    }

    public static function opciones_campo_select()
    {
        $opciones = ProgramacionAulaVirtual::where('sga_programacion_aula_virtual.estado','Activo')
                    ->select('sga_programacion_aula_virtual.id','sga_programacion_aula_virtual.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
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


    public static function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $user = Auth::user();

        /*
            Personalizar los campos
        */
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            switch ($lista_campos[$i]['name'])
            {
                case 'actividad_escolar_id':
                	if ( $registro->actividad_escolar_id != 0 )
                	{
                		$lista_campos[$i]['value'] = $registro->actividad_escolar->descripcion;
	                    $lista_campos[$i]['tipo'] = 'bsText';
	                    $lista_campos[$i]['atributos'] = ['disabled' => 'disabled', 'style' => 'background-color:#FBFBFB;'];
                	}
	                    
                    break;

                case 'guia_academica_id':
                	if ( $registro->guia_academica_id != 0 )
                	{
                		$lista_campos[$i]['value'] = $registro->guia_academica->descripcion;
	                    $lista_campos[$i]['tipo'] = 'bsText';
	                    $lista_campos[$i]['atributos'] = ['disabled','disabled'];
                	}
	                    
                    break;

                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }
}
