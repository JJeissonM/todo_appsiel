<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use Input;
use App\User;

use App\Calificaciones\Area;
use App\Calificaciones\Asignatura;
use App\Calificaciones\CursoTieneAsignatura;
use App\Core\Foro;
use App\AcademicoDocente\AsignacionProfesor;

class Curso extends Model
{
    protected $table = 'sga_cursos';
    
    protected $fillable = [ 'id_colegio', 'nivel_grado', 'sga_grado_id', 'codigo', 'descripcion', 'maneja_calificacion', 'imagen', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nivel', 'Grado', 'Descripcion', 'Código', 'Maneja Calificacion (0=No, 1=Si)', 'Estado'];

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'sga_grado_id');
    }

    public function nivel()
    {
        return $this->belongsTo(NivelAcademico::class, 'nivel_grado');
    }

    public function foros()
    {
        return $this->hasMany(Foro::class);
    }

    public function asignaturas_asignadas()
    {
        return $this->hasMany(CursoTieneAsignatura::class, 'curso_id')->orderBy('orden_boletin');
    }

    public function director_grupo()
    {
        return $this->belongsToMany('App\User', 'sga_curso_tiene_director_grupo', 'curso_id', 'user_id');
    }

    /**/
    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Curso::leftJoin('sga_niveles', 'sga_niveles.id', '=', 'sga_cursos.nivel_grado')
            ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_cursos.sga_grado_id')
            ->orderBy('sga_cursos.nivel_grado', 'ASC')
            ->select(
                'sga_niveles.descripcion AS campo1',
                'sga_grados.descripcion AS campo2',
                'sga_cursos.descripcion AS campo3',
                'sga_cursos.codigo AS campo4',
                'sga_cursos.maneja_calificacion AS campo5',
                'sga_cursos.estado AS campo6',
                'sga_cursos.id AS campo7'
            )->where("sga_niveles.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.codigo", "LIKE", "%$search%")
            ->orWhere("sga_cursos.maneja_calificacion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.estado", "LIKE", "%$search%")
            ->orderBy('sga_cursos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Curso::leftJoin('sga_niveles', 'sga_niveles.id', '=', 'sga_cursos.nivel_grado')
            ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_cursos.sga_grado_id')
            ->orderBy('sga_cursos.nivel_grado', 'ASC')
            ->select(
                'sga_niveles.descripcion AS NIVEL',
                'sga_grados.descripcion AS GRADO',
                'sga_cursos.descripcion AS CURSO',
                'sga_cursos.codigo AS CÓDIGO_CURSO',
                'sga_cursos.maneja_calificacion AS MANEJA_CAL_(0=NO, 1=SI)',
                'sga_cursos.estado AS ESTADO CURSO'
            )
            ->where("sga_niveles.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.codigo", "LIKE", "%$search%")
            ->orWhere("sga_cursos.maneja_calificacion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.estado", "LIKE", "%$search%")
            ->orderBy('sga_cursos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CURSOS O GRUPOS ACADÉMICOS";
    }

    public static function get_array_to_select()
    {
        $opciones = Curso::where('estado','=','Activo')
                            ->orderBy('descripcion')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->descripcion;
        }
        
        return $vec;
    }

    public static function opciones_campo_select()
    {
        $user = Auth::user();

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
        {
            $carga_academica_profesor = AsignacionProfesor::get_asignaturas_x_curso( $user->id );

            $vec_cursos = $carga_academica_profesor->pluck('curso_id')->toArray();

            $opciones = Curso::where('estado','=','Activo')
                                ->whereIn('id', $vec_cursos)
                                ->orderBy('descripcion')
                                ->get();
        } else {
            $opciones = Curso::where('estado','=','Activo')
                                ->orderBy('descripcion')
                                ->get();
        }

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }
        
        return $vec;
    }

    public static function select_curso_del_grado( $grado_id, $curso_id = 0 )
    {

        $opciones = Curso::where([
                                    ['estado','=','Activo'],
                                    ['sga_grado_id','=', $grado_id]
                                ])
                            ->orderBy('descripcion')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            if ( $opcion->id != $curso_id )
            {
                $vec[$opcion->id] = $opcion->descripcion;
            }                
        }
        
        return $vec;
    }

    public static function get_registros_estado_activo()
    {
        return Curso::where('estado', 'Activo')
                    ->OrderBy('descripcion')
                    ->get();
    }

    public static function get_registros_del_periodo_lectivo( $periodo_lectivo_id )
    {
        return CursoTieneAsignatura::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_curso_tiene_asignaturas.periodo_lectivo_id')
                            ->leftJoin('sga_cursos','sga_cursos.id','=','sga_curso_tiene_asignaturas.curso_id')
                            ->where( 'sga_curso_tiene_asignaturas.periodo_lectivo_id', $periodo_lectivo_id )
                            ->select(
                                        'sga_cursos.descripcion',
                                        'sga_cursos.id',
                                        'sga_periodos_lectivos.descripcion as periodo_lectivo_descripcion',
                                        'sga_curso_tiene_asignaturas.periodo_lectivo_id')
                            ->groupBy('sga_curso_tiene_asignaturas.curso_id')
                            ->orderBy('sga_cursos.descripcion','ASC')
                            ->get();
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Bloques_eeff
    */

        // Tabla para visualizar registros asignados
        // En la vista show del modelo padre
    public static function get_tabla($registro_modelo_padre,$registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="lista_asignaciones">
                    <thead>';
                        $encabezado_tabla = ['ID','Nombre','Email','Acción'];
                        for($i=0;$i<count($encabezado_tabla);$i++){
                            $tabla.='<th>'.$encabezado_tabla[$i].'</th>';
                        }
                $tabla.='</thead>
                    <tbody>';
                        $ih_total = 0;
                        foreach($registros_asignados as $fila){

                            $tabla.='<tr>';
                            $tabla.='<td>'.$fila['id'].'</td>';
                            $tabla.='<td>'.$fila['name'].'</td>';
                            $tabla.='<td>'.$fila['email'].'</td>';
                            $tabla.='<td>
                                    <a class="btn btn-danger btn-sm" href="'.url('web/eliminar_asignacion/registro_modelo_hijo_id/'.$fila['id'].'/registro_modelo_padre_id/'.$registro_modelo_padre->id.'/id_app/'.Input::get('id').'/id_modelo_padre/'.Input::get('id_modelo')).'"><i class="fa fa-btn fa-trash"></i> </a>
                                    </td>
                                </tr>';
                        }
                        $tabla.='</tbody>';

                    $tabla.='</table>
            </div>';
        return $tabla;
    }


    public static function get_tabla_asignaturas_asignadas($curso_id, $registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="lista_asignaciones">
                    <thead>';
                        $encabezado_tabla = ['Orden boletín','Área','Descripción','Intensidad horaria','Maneja calificación','Acción'];
                        for($i=0;$i<count($encabezado_tabla);$i++){
                            $tabla.='<th>'.$encabezado_tabla[$i].'</th>';
                        }
                $tabla.='</thead>
                    <tbody>';
                        $ih_total = 0;
                        foreach($registros_asignados as $fila){
                            
                            if ( $fila->maneja_calificacion == 1 ) 
                            {
                                $maneja_calificacion = 'Si';
                            }else{
                                $maneja_calificacion = 'No';
                            }

                            $asignatura = Asignatura::where('id',$fila->asignatura_id)->get()[0];

                            $area = Area::find($asignatura->area_id);

                            $tabla.='<tr>';
                            $tabla.='<td>'.$fila->orden_boletin.'</td>';
                            $tabla.='<td>'.$area->descripcion.'</td>';
                            $tabla.='<td>'.$asignatura->descripcion.'</td>';
                            $tabla.='<td>'.$fila->intensidad_horaria.'</td>';
                            $tabla.='<td>'.$maneja_calificacion.'</td>';
                            $tabla.='<td>
                                    <button class="btn btn-danger btn-sm eliminar" id="'.$curso_id.'-'.$asignatura->id.'"><i class="fa fa-btn fa-trash"></i> </button>
                                    </td>
                            </tr>';
                            $ih_total+=$fila->intensidad_horaria;
                        }
                        $tabla.='</tbody>';
                        
                        $tabla.='<tfoot><tr><td colspan="3"></td>';
                            $tabla.='<td><div id="ih_total">'.$ih_total.'</div></td>';
                            $tabla.='<td colspan="2">
                                    </td>
                            </tr></tfoot>';

                    $tabla.='</table>
            </div>';
        return $tabla;
    }

    // Opciones del select para asignar nuevos hijos
    public static function get_opciones_modelo_relacionado($curso_id)
    {
        $vec['']='';
        $opciones = User::all();
        foreach ($opciones as $opcion)
        {
            if ( $opcion->hasRole('Director de grupo') ) 
            {
                $esta = DB::table('sga_curso_tiene_director_grupo')->where('curso_id',$curso_id)->where('user_id',$opcion->id)->get();
                if ( empty($esta) )
                {
                    $vec[$opcion->id]=$opcion->name;               
                }
            }
        }

        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'sga_curso_tiene_director_grupo';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'curso_id';
        $registro_modelo_hijo_id = 'user_id';

        return compact('nombre_tabla','nombre_columna1','registro_modelo_padre_id','registro_modelo_hijo_id');
    }

    public static function get_cursos_x_grado( $grado_id )
    {
        return Curso::where( 'sga_grado_id', $grado_id)
                    ->where('estado','Activo')
                    ->get();
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"sga_actividades_escolares",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene actividades escolares relacionadas."
                                },
                            "1":{
                                    "tabla":"sga_asignaciones_profesores",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene Profesor asignado."
                                },
                            "2":{
                                    "tabla":"sga_asistencia_clases",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene asistencia a clases relacionadas."
                                },
                            "3":{
                                    "tabla":"sga_logros",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene logros relacionadas."
                                },
                            "4":{
                                    "tabla":"sga_calificaciones",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Ya Tiene calificaciones."
                                },
                            "5":{
                                    "tabla":"sga_calificaciones_auxiliares",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Ya Tiene calificaciones."
                                },
                            "6":{
                                    "tabla":"sga_calificaciones_auxiliares",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Ya Tiene calificaciones."
                                },
                            "7":{
                                    "tabla":"sga_calificaciones_encabezados",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene encabezados de calificaciones relacionados."
                                },
                            "8":{
                                    "tabla":"sga_control_disciplinario",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene anotaciones de control disciplinario."
                                },
                            "9":{
                                    "tabla":"sga_curso_tiene_asignaturas",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Ya pertence a un curso."
                                },
                            "10":{
                                    "tabla":"sga_curso_tiene_director_grupo",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene director de grupo asignado."
                                },
                            "11":{
                                    "tabla":"sga_matriculas",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene matrículas relacionadas."
                                },
                            "12":{
                                    "tabla":"sga_metas",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene metas(propósitos) relaciondas."
                                },
                            "13":{
                                    "tabla":"sga_observaciones_boletines",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene informes relacionados."
                                },
                            "14":{
                                    "tabla":"sga_observaciones_ingresadas",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene informes relacionados."
                                },
                            "15":{
                                    "tabla":"sga_preinformes_academicos",
                                    "llave_foranea":"curso_id",
                                    "mensaje":"Tiene registros en preinformes académicos."
                                }
                        }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }


}
