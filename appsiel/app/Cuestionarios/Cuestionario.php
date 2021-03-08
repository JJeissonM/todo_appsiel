<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;

use App\Cuestionarios\Pregunta;
use App\Cuestionarios\CuestionarioTienePregunta;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class Cuestionario extends Model
{
    protected $table = 'sga_cuestionarios'; 

    protected $fillable = ['colegio_id','descripcion','detalle','activar_resultados','estado','created_by'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Resultados activados (Bloqueado al estudiante)', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $user = Auth::user();

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
        {
            $collection = Cuestionario::where('created_by', Auth::user()->id)
                                    ->select(
                                            'sga_cuestionarios.descripcion AS campo1',
                                            'sga_cuestionarios.activar_resultados AS campo2',
                                            'sga_cuestionarios.estado AS campo3',
                                            'sga_cuestionarios.id AS campo4'
                                        )->get();
        } else {
            $collection = Cuestionario::select(
                                                'sga_cuestionarios.descripcion AS campo1',
                                                'sga_cuestionarios.activar_resultados AS campo2',
                                                'sga_cuestionarios.estado AS campo3',
                                                'sga_cuestionarios.id AS campo4'
                                            )->get();
        }       

        if (count($collection) > 0)
        {
            foreach ($collection as $c)
            {
                $c->campo2 = 'No';

                if ( $c->campo2 )
                {
                    $c->campo2 = 'Si';
                }
            }
        }
        
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
        $string = Cuestionario::where('created_by', Auth::user()->id)
            ->select(
                'sga_cuestionarios.descripcion AS NOMBRE',
                'sga_cuestionarios.estado AS ESTADO'
            )
            ->orWhere("sga_cuestionarios.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cuestionarios.estado", "LIKE", "%$search%")
            ->orderBy('sga_cuestionarios.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CUESTIONARIOS";
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/calificaciones/actividades_escolares/cuestionarios.js';

    public function preguntas()
    {
        return $this->belongsToMany('App\Cuestionarios\Pregunta','sga_cuestionario_tiene_preguntas');
    }


    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Campo
    */
    public static function get_tabla($registro_modelo_padre,$registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="myTable">
                    <thead>';
                        $encabezado_tabla = ['Orden','ID','Descripción','Tipo','Opciones','Opción correcta','Estado','Acción'];
                        for($i=0;$i<count($encabezado_tabla);$i++){
                            $tabla.='<th>'.$encabezado_tabla[$i].'</th>';
                        }
                $tabla.='</thead>
                    <tbody>';
                        foreach($registros_asignados as $fila){
                            $orden = DB::table('sga_cuestionario_tiene_preguntas')
                                        ->where('cuestionario_id', '=', $registro_modelo_padre->id)
                                        ->where('pregunta_id', '=', $fila['id'])
                                        ->value('orden');

                            $tabla.='<tr>';
                                $tabla.='<td>'.$orden.'</td>';
                                $tabla.='<td>'.$fila['id'].'</td>';
                                $tabla.='<td>'.$fila['descripcion'].'</td>';
                                $tabla.='<td>'.$fila['tipo'].'</td>';
                                $tabla.='<td>'.$fila['opciones'].'</td>';
                                $tabla.='<td>'.$fila['respuesta_correcta'].'</td>';
                                $tabla.='<td>'.$fila['estado'].'</td>';
                                $tabla.='<td>
                                        <a class="btn btn-danger btn-sm" href="'.url('web/eliminar_asignacion/registro_modelo_hijo_id/'.$fila['id'].'/registro_modelo_padre_id/'.$registro_modelo_padre->id.'/id_app/'.Input::get('id').'/id_modelo_padre/'.Input::get('id_modelo')).'"><i class="fa fa-btn fa-trash"></i> </a>
                                        </td>
                            </tr>';
                        }
                    $tabla.='</tbody>
                </table>
            </div>';
        return $tabla;
    }

    public static function get_opciones_modelo_relacionado($cuestionario_id)
    {
        $vec['']='';
        $opciones = Pregunta::where( 'created_by', Auth::user()->id )
                                ->where( 'estado', 'Activo' )
                                ->get();
        foreach ($opciones as $opcion)
        {
            $esta = CuestionarioTienePregunta::where('cuestionario_id',$cuestionario_id)->where('pregunta_id',$opcion->id)->get();
            
            if ( empty( $esta->toArray() ) )
            {
                $vec[$opcion->id]=$opcion->descripcion;
            }
        }

        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'sga_cuestionario_tiene_preguntas';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'cuestionario_id';
        $registro_modelo_hijo_id = 'pregunta_id';

        return compact('nombre_tabla','nombre_columna1','registro_modelo_padre_id','registro_modelo_hijo_id');
    }

    
    public static function opciones_campo_select()
    {
        $opciones = Cuestionario::where('created_by', Auth::user()->id)->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}