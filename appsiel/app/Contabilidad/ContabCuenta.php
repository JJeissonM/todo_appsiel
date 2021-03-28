<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use Schema;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ContabCuenta extends Model
{
    protected $fillable = ['core_empresa_id', 'codigo', 'contab_cuenta_clase_id', 'contab_cuenta_grupo_id', 'descripcion', 'core_app_id', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Clase', 'Grupo', 'Código', 'Descripción', 'Aplicación asociada'];

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = ContabCuenta::leftJoin('contab_cuenta_clases', 'contab_cuenta_clases.id', '=', 'contab_cuentas.contab_cuenta_clase_id')
                                    ->leftJoin('contab_cuenta_grupos', 'contab_cuenta_grupos.id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
                                    ->leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'contab_cuentas.core_app_id')
                                    ->select(
                                        'contab_cuenta_clases.descripcion AS campo1',
                                        'contab_cuenta_grupos.descripcion AS campo2',
                                        'contab_cuentas.codigo AS campo3',
                                        'contab_cuentas.descripcion AS campo4',
                                        'sys_aplicaciones.descripcion AS campo5',
                                        'contab_cuentas.id AS campo6'
                                    )
                                    ->orderBy('contab_cuentas.created_at', 'DESC')
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
        $string = ContabCuenta::leftJoin('contab_cuenta_clases', 'contab_cuenta_clases.id', '=', 'contab_cuentas.contab_cuenta_clase_id')
            ->leftJoin('contab_cuenta_grupos', 'contab_cuenta_grupos.id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
            ->leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'contab_cuentas.core_app_id')
            ->where('contab_cuentas.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'contab_cuenta_clases.descripcion AS CLASE',
                'contab_cuenta_grupos.descripcion AS GRUPO',
                'contab_cuentas.codigo AS CÓDIGO',
                'contab_cuentas.descripcion AS DESCRIPCIÓN',
                'sys_aplicaciones.descripcion AS APLICACIÓN_ASOCIADA',
                'contab_cuentas.id AS ID'
            )
            ->orWhere("contab_cuenta_clases.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_cuenta_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_cuentas.codigo", "LIKE", "%$search%")
            ->orWhere("contab_cuentas.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orderBy('contab_cuentas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CUENTAS CONTABLES";
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/contabilidad/funciones.js';

    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = DB::table('contab_cuenta_grupos')
            ->where('contab_cuenta_clase_id', $id_select_padre)
            ->where('core_empresa_id', '=', Auth::user()->empresa_id)
            ->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {
            $grupo = DB::table('contab_cuenta_grupos')
                ->where('id', $campo->grupo_padre_id)
                ->value('descripcion');

            $opciones .= '<option value="' . $campo->id . '">' . $grupo . ' > ' . $campo->descripcion . '</option>';
        }
        return $opciones;
    }

    public static function opciones_campo_select()
    {
        $opciones = ContabCuenta::where('contab_cuentas.core_empresa_id', Auth::user()->empresa_id)
            ->select('contab_cuentas.id', 'contab_cuentas.codigo', 'contab_cuentas.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->codigo . ' ' . $opcion->descripcion;
        }

        return $vec;
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"contab_doc_registros",
                                    "llave_foranea":"contab_cuenta_id",
                                    "mensaje":"Cuenta tiene registros en documentos de contabilidad."
                                },
                            "1":{
                                    "tabla":"contab_movimientos",
                                    "llave_foranea":"contab_cuenta_id",
                                    "mensaje":"Tiene movimientos contables."
                                },
                            "2":{
                                    "tabla":"cxc_servicios",
                                    "llave_foranea":"contab_cuenta_id",
                                    "mensaje":"Cuenta está asociada a servicios de CxC."
                                },
                            "3":{
                                    "tabla":"nom_equivalencias_contables",
                                    "llave_foranea":"contab_cuenta_id",
                                    "mensaje":"Está asociada a Equivalencias contables de nómina."
                                },
                            "4":{
                                    "tabla":"teso_cajas",
                                    "llave_foranea":"contab_cuenta_id",
                                    "mensaje":"Está asociada a una Caja en Tesorería."
                                },
                            "5":{
                                    "tabla":"teso_cuentas_bancarias",
                                    "llave_foranea":"contab_cuenta_id",
                                    "mensaje":"Está asociada a una Cuenta bancaria en Tesorería."
                                },
                            "6":{
                                    "tabla":"teso_motivos",
                                    "llave_foranea":"contab_cuenta_id",
                                    "mensaje":"Está asociada a un Motivo de Tesorería."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla)
        {

            if ( !Schema::hasTable( $una_tabla->tabla ) )
            {
                continue;
            }

            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro))
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
