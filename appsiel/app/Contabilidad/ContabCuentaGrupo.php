<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContabCuentaGrupo extends Model
{
    protected $fillable = ['core_empresa_id', 'contab_cuenta_clase_id', 'grupo_padre_id', 'descripcion', 'mostrar_en_reporte', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Clase', 'Padre', 'Descripción'];

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/contabilidad/funciones.js';

    public function grupo_padre()
    {
        return $this->belongsTo(ContabCuentaGrupo::class, 'grupo_padre_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = ContabCuentaGrupo::leftJoin('contab_cuenta_grupos AS grupos_padres', 'grupos_padres.id', '=', 'contab_cuenta_grupos.grupo_padre_id')
                                    ->leftJoin('contab_cuenta_clases', 'contab_cuenta_clases.id', '=', 'contab_cuenta_grupos.contab_cuenta_clase_id')
                                    ->where('contab_cuenta_grupos.core_empresa_id', Auth::user()->empresa_id)
                                    ->select(
                                                'contab_cuenta_clases.descripcion as campo1',
                                                DB::raw('CONCAT(grupos_padres.id," ",grupos_padres.descripcion) AS campo2'),
                                                DB::raw('CONCAT(contab_cuenta_grupos.id," ",contab_cuenta_grupos.descripcion) AS campo3'),
                                                'contab_cuenta_grupos.id AS campo4'
                                            )
                                    ->orderBy('contab_cuenta_grupos.contab_cuenta_clase_id')
                                    ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if ( self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
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
        $string = ContabCuentaGrupo::leftJoin('contab_cuenta_grupos AS grupos_padres', 'grupos_padres.id', '=', 'contab_cuenta_grupos.grupo_padre_id')
        ->leftJoin('contab_cuenta_clases', 'contab_cuenta_clases.id', '=', 'contab_cuenta_grupos.contab_cuenta_clase_id')
            ->where('contab_cuenta_grupos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'contab_cuenta_clases.descripcion AS CLASE',
                'grupos_padres.descripcion as PADRE',
                'contab_cuenta_grupos.descripcion AS DESCRIPCIÓN',
                'contab_cuenta_grupos.id AS ID',
                'grupos_padres.id AS PADRE_ID'
            )
            ->orWhere("contab_cuenta_clases.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_cuenta_grupos.grupo_padre_id", "LIKE", "%$search%")
            ->orWhere("contab_cuenta_grupos.descripcion", "LIKE", "%$search%")
            ->orderBy('contab_cuenta_grupos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE GRUPO CUENTAS";
    }

    public static function opciones_campo_select()
    {

        // MEJORAR PARA QUE MUESTRE LOS GRUPOS PADRES

        $opciones = ContabCuentaGrupo::where('core_empresa_id', Auth::user()->empresa_id)->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = DB::table('contab_cuenta_grupos')
            ->where('contab_cuenta_clase_id', $id_select_padre)
            ->where('core_empresa_id', '=', Auth::user()->empresa_id)
            ->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo)
        {
            $grupo = DB::table('contab_cuenta_grupos')
                ->where('id', $campo->grupo_padre_id)
                ->value('descripcion');

            $opciones .= '<option value="' . $campo->id . '">' . $grupo . ' > ' . $campo->descripcion . '</option>';
        }
        return $opciones;
    }
}
