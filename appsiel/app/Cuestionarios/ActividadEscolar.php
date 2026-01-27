<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use App\Calificaciones\CursoTieneAsignatura;

use App\Matriculas\PeriodoLectivo;
use App\Calificaciones\Periodo;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class ActividadEscolar extends Model
{
    protected $table = 'sga_actividades_escolares'; 

    protected $fillable = ['descripcion','tematica','instrucciones','tipo_recurso','url_recurso','cuestionario_id','fecha_entrega','fecha_desde','fecha_hasta','periodo_id','curso_id','asignatura_id','estado','created_by'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Título', 'Temática', 'Fecha programada', 'Fecha de entrega', 'Periodo', 'Curso', 'Asignatura', 'Estado'];

    public $urls_acciones = '{"cambiar_estado":"a_i/id_fila"}';

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/calificaciones/actividades_escolares/actividades.js';

    public function cuestionario()
    {
        return $this->belongsTo('App\Cuestionarios\Cuestionario','cuestionario_id');
    }

    public function curso()
    {
        return $this->belongsTo('App\Calificaciones\Curso','curso_id');
    }

    public function asignatura()
    {
        return $this->belongsTo('App\Calificaciones\Asignatura','asignatura_id');
    }

    public function periodo()
    {
        return $this->belongsTo('App\Calificaciones\Periodo','periodo_id');
    }    

    public function estudiantes()
    {
        return $this->belongsToMany('App\Matriculas\Estudiante','estudiante_tiene_actividades_escolares','actividad_escolar_id','estudiante_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {

        $periodos = Periodo::select('id')->get()->pluck('id');

        // Filtros por Perfil de usuario
        $user = Auth::user();
        $array_wheres = [
            ['sga_actividades_escolares.id', '>', 0]
        ];

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
        {
            $array_wheres = array_merge($array_wheres, ['sga_actividades_escolares.created_by' => $user->id]);
            
            // Filtro año lectivo actual
            $periodo_lectivo_actual = PeriodoLectivo::get_actual();

            $periodos = Periodo::where('periodo_lectivo_id', $periodo_lectivo_actual->id)->select('id')->get()->pluck('id');
        }

        $collection = ActividadEscolar::leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_actividades_escolares.curso_id')
                            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_actividades_escolares.asignatura_id')
                            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_actividades_escolares.periodo_id')
                            ->where($array_wheres)
                            ->whereIn('sga_actividades_escolares.periodo_id', $periodos)
                            ->select(
                                'sga_actividades_escolares.descripcion AS campo1',
                                'sga_actividades_escolares.tematica AS campo2',
                                'sga_actividades_escolares.fecha_desde AS campo3',
                                'sga_actividades_escolares.fecha_entrega AS campo4',
                                'sga_periodos.descripcion AS campo5',
                                'sga_cursos.descripcion AS campo6',
                                'sga_asignaturas.descripcion AS campo7',
                                'sga_actividades_escolares.estado AS campo8',
                                'sga_actividades_escolares.id AS campo9'
                            )
                            //->distinct('core_acl.user_id')
                            ->orderBy('sga_actividades_escolares.created_at', 'DESC')
                            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if ( self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search) ) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if ( empty($nuevaColeccion) )
        {
            return $array = new LengthAwarePaginator([], 1, 1, 1, [
                                                                    'path' => $request->url(),
                                                                    'query' => $request->query(),
                                                                ]);
        }
        
        //obtenemos el numero de la página actual, por defecto 1
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $total = count($nuevaColeccion); //Total para contar los registros mostrados
        $starting_point = ($page * $nro_registros) - $nro_registros; // punto de inicio para mostrar registros
        $array = $nuevaColeccion->slice($starting_point, $nro_registros); //indicamos desde donde y cuantos registros mostrar
        $array = new LengthAwarePaginator($array, $total, $nro_registros, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]); //finalmente se pagina y organiza la coleccion a devolver con todos los datos

        return $array;
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param array $valores_campos_seleccionados de campos donde se busca
     * @param string $searchTerm termino de busqueda
     * @return bool
     */
    public static function likePhp($valores_campos_seleccionados, $searchTerm)
    {
        $encontrado = false;
        $searchTerm = str_slug($searchTerm); // Para eliminar acentos
        foreach ($valores_campos_seleccionados as $valor_campo)
        {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false)
            {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    public static function sqlString($search)
    {
        // Filtro año lectivo actual
        $periodo_lectivo_actual = PeriodoLectivo::get_actual();

        $periodos = Periodo::where('periodo_lectivo_id', $periodo_lectivo_actual->id)->select('id')->get()->pluck('id');

        // Filtros por Perfil de usuario
        $user = Auth::user();
        $array_wheres = [
            ['sga_actividades_escolares.id', '>', 0]
        ];

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
        {
            $array_wheres = array_merge($array_wheres, ['sga_actividades_escolares.created_by' => $user->id]);
        }

        $string = ActividadEscolar::leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_actividades_escolares.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_actividades_escolares.asignatura_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_actividades_escolares.periodo_id')
            ->where($array_wheres)
            ->whereIn('sga_actividades_escolares.periodo_id', $periodos)
            ->select(
                'sga_actividades_escolares.descripcion AS TÍTULO',
                'sga_actividades_escolares.tematica AS TEMÁTICA',
                'sga_actividades_escolares.fecha_entrega AS FECHA_DE_ENTREGA',
                'sga_periodos.descripcion AS PERIODO',
                'sga_cursos.descripcion AS CURSO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_actividades_escolares.estado AS ESTADO'
            )
            ->where("sga_actividades_escolares.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_actividades_escolares.tematica", "LIKE", "%$search%")
            ->orWhere("sga_actividades_escolares.fecha_entrega", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_actividades_escolares.estado", "LIKE", "%$search%")
            ->orderBy('sga_actividades_escolares.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }


    public static function opciones_campo_select()
    {
        $vec['']=''; // La actividad escolar depende del curso y la asignatura
        
        return $vec;
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ACTIVIDADES ESCOLARES";
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