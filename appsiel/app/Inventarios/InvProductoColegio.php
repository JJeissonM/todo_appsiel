<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Auth;

use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Support\Facades\DB;

class InvProductoColegio extends Model
{
    protected $table = 'inv_productos'; 

    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'REF.', 'Grado', 'Asignatura', 'Descripción', 'Editorial', 'Código barras', 'Categoría', 'Cantidad', 'Estado'];

    // unidad_medida1 = consecutivo manual, como una referencia
    // unidad_medida2 = Editorial
    // categoria_id = Grado
    // impuesto_id = asignatura_id
    // referencia = Cantidad. Se ejecutar proceso de entrada incial automática: crear documento EA y dejar en cero este campo.
    // precio_compra = 77.77  usado para indicar que es un Elemento de biblioteca
    public static function consultar_registros($nro_registros, $search)
    {
        $array_wheres = [
                            ['inv_productos.precio_compra', '=', 77.77],
                            ['inv_productos.core_empresa_id', Auth::user()->empresa_id]
                        ];

        $collection =  InvProductoColegio::leftJoin('sga_grados', 'sga_grados.id', '=', 'inv_productos.categoria_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'inv_productos.impuesto_id')
            ->leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->where($array_wheres)
            ->select(
                        'inv_productos.unidad_medida1 AS campo1',
                        DB::raw('CONCAT(sga_grados.descripcion," (",sga_grados.codigo,")") AS campo2'),
                        DB::raw('CONCAT(sga_asignaturas.descripcion," (",sga_asignaturas.id,")") AS campo3'),
                        'inv_productos.descripcion AS campo4',
                        'inv_productos.unidad_medida2 AS campo5',
                        'inv_productos.codigo_barras AS campo6',
                        'inv_grupos.descripcion AS campo7',
                        'inv_productos.referencia AS campo8',
                        'inv_productos.estado AS campo9',
                        'inv_productos.id AS campo10'
                    )
                    ->orderBy('sga_grados.descripcion', 'DESC')
                    ->orderBy('sga_asignaturas.descripcion', 'DESC')
            ->orderBy('inv_productos.unidad_medida1', 'ASC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8, $c->campo9, $c->campo10], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if (empty($nuevaColeccion)) {
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
        foreach ($valores_campos_seleccionados as $valor_campo) {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false) {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    public static function sqlString($search)
    {
        $array_wheres = [
            ['inv_productos.precio_compra', '=', 77.77],
            ['inv_productos.core_empresa_id', Auth::user()->empresa_id]
        ];
        $string = InvProductoColegio::leftJoin('sga_grados', 'sga_grados.id', '=', 'inv_productos.categoria_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'inv_productos.impuesto_id')
            ->leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->where($array_wheres)
            ->select(
                'inv_productos.unidad_medida1 AS REF.',
                DB::raw('CONCAT(sga_grados.descripcion," (",sga_grados.codigo,")") AS GRADO'),
                DB::raw('CONCAT(sga_asignaturas.descripcion," (",sga_asignaturas.id,")") AS ASIGNATURA'),
                'inv_productos.descripcion AS DESCRIPCIÓN',
                'inv_productos.unidad_medida2 AS EDITORIAL',
                'inv_grupos.descripcion AS CATEGORIA',
                'inv_productos.codigo_barras AS CÓDIGO_BARRAS',
                'inv_productos.referencia AS CANTIDAD'
            )
            ->where("inv_productos.id", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.unidad_medida2", "LIKE", "%$search%")
            ->orWhere("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.codigo_barras", "LIKE", "%$search%")
            ->orWhere("inv_productos.referencia", "LIKE", "%$search%")
            ->orderBy('sga_grados.descripcion', 'DESC')
            ->orderBy('sga_asignaturas.descripcion', 'DESC')
            ->orderBy('inv_productos.unidad_medida1', 'ASC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ELEMENTOS BIBLIOTECA";
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvProductoColegio::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
