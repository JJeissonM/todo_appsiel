<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class Pregunta extends Model
{
    protected $table = 'sga_preguntas'; 

    protected $fillable = ['descripcion','tipo','opciones','respuesta_correcta','estado','created_by'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Tipo', 'Opciones', 'Estado'];

    public $urls_acciones = '{"cambiar_estado":"a_i/id_fila"}';

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/calificaciones/actividades_escolares/preguntas.js';

    public function cuestionarios()
    {
        return $this->belongsToMany('App\Cuestionarios\Cuestionario','sga_cuestionario_tiene_preguntas');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $user = Auth::user();

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
        {
            $collection = Pregunta::where('created_by', $user->id)
                            ->select(
                                'sga_preguntas.descripcion AS campo1',
                                'sga_preguntas.tipo AS campo2',
                                'sga_preguntas.opciones AS campo3',
                                'sga_preguntas.estado AS campo4',
                                'sga_preguntas.id AS campo5'

                            )
                            ->orderBy('sga_preguntas.created_at', 'DESC')
                            ->get();
        }else{
            $collection = Pregunta::select(
                                'sga_preguntas.descripcion AS campo1',
                                'sga_preguntas.tipo AS campo2',
                                'sga_preguntas.opciones AS campo3',
                                'sga_preguntas.estado AS campo4',
                                'sga_preguntas.id AS campo5'

                            )
                            ->orderBy('sga_preguntas.created_at', 'DESC')
                            ->get(); 
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
        $string = Pregunta::where('created_by', Auth::user()->id)
            ->select(
                'sga_preguntas.descripcion AS Descripción',
                'sga_preguntas.tipo AS Tipo',
                'sga_preguntas.opciones AS Opciones',
                'sga_preguntas.estado AS Estado'
            )
            ->orWhere("sga_preguntas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_preguntas.tipo", "LIKE", "%$search%")
            ->orWhere("sga_preguntas.opciones", "LIKE", "%$search%")
            ->orWhere("sga_preguntas.estado", "LIKE", "%$search%")
            ->orderBy('sga_preguntas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PREGUNTAS";
    }
}
