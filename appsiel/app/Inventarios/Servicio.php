<?php

namespace App\Inventarios;

use App\Inventarios\Services\CodigoBarras;
use App\Ventas\ListaPrecioDetalle;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class Servicio extends Model
{
    protected $table = 'inv_productos'; 

    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Referencia', 'Descripción', 'UM-1', 'Grupo', '% Rentabilidad', 'Precio venta', 'IVA', 'Tipo', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_productos.tipo', 'servicio')
            ->select(
                        'inv_productos.id AS campo1',
                        'inv_productos.referencia AS campo2',
                        'inv_productos.descripcion AS campo3',
                        'inv_productos.unidad_medida1 AS campo4',
                        'inv_grupos.descripcion AS campo5',
                        'inv_productos.precio_compra AS campo6',
                        'inv_productos.precio_venta AS campo7',
                        'contab_impuestos.tasa_impuesto AS campo8',
                        'inv_productos.tipo AS campo9',
                        'inv_productos.estado AS campo10',
                        'inv_productos.id AS campo11'
                    )
            ->orderBy('inv_productos.created_at', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8, $c->campo9], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        foreach( $nuevaColeccion AS $register_collect )
        {
            $item = InvProducto::find( $register_collect->campo1 );
            $register_collect->campo4 = $item->get_unidad_medida1();
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
        $string = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_productos.tipo', 'servicio')
            ->select(
                'inv_productos.id AS CÓDIGO',
                'inv_productos.referencia AS REFERENCIA',
                'inv_productos.descripcion AS DESCRIPCIÓN',
                'inv_productos.unidad_medida1 AS UM-1',
                'inv_grupos.descripcion AS GRUPO_INVENTARIO',
                'inv_productos.precio_compra AS PRECIO_COMPRA',
                'inv_productos.precio_venta AS PRECIO_VENTA',
                'contab_impuestos.tasa_impuesto AS IVA',
                'inv_productos.tipo AS TIPO',
                'inv_productos.estado AS ESTADO'
            )
            ->where("inv_productos.id", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.referencia", "LIKE", "%$search%")
            ->orWhere("inv_productos.unidad_medida1", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_compra", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_venta", "LIKE", "%$search%")
            ->orWhere("contab_impuestos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("inv_productos.tipo", "LIKE", "%$search%")
            ->orWhere("inv_productos.estado", "LIKE", "%$search%")
            ->orderBy('inv_productos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE SERVICIOS";
    }

    public static function opciones_campo_select()
    {
        $opciones = InvProducto::where('estado','Activo')
                            ->where('tipo','servicio')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion;
        }

        return $vec;
    }

    public static function store_adicional($datos, $registro)
    {        
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            ListaPrecioDetalle::create([
                'lista_precios_id' => (int)config('ventas.lista_precios_id'),
                'inv_producto_id' => $registro->id,
                'fecha_activacion' => date('Y-m-d'),
                'precio' => $registro->precio_venta
            ]);
        }
    }

    public function update_adicional( $datos, $id )
    {
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $nuevo_precio_venta = 0;
            if (isset($datos['precio_venta'])) {
                $nuevo_precio_venta = $datos['precio_venta'];
            }

            $reg_precio_actual = ListaPrecioDetalle::where([
                ['lista_precios_id', '=', (int)config('ventas.lista_precios_id')],
                ['inv_producto_id', '=', $id]
            ])
            ->get()
            ->last();

            if ($reg_precio_actual == null) {
                ListaPrecioDetalle::create([
                    'lista_precios_id' => (int)config('ventas.lista_precios_id'),
                    'inv_producto_id' => $id,
                    'fecha_activacion' => date('Y-m-d'),
                    'precio' => $nuevo_precio_venta
                ]);
            }else{
                if ($nuevo_precio_venta != $reg_precio_actual->precio) {
                    $reg_precio_actual->precio = $nuevo_precio_venta;
                    $reg_precio_actual->save();
                }
            }
        }
    }
    
}
