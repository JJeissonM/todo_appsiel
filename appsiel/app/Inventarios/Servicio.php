<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;

use App\Inventarios\InvGrupo;

use App\Contabilidad\Impuesto;


class Servicio extends Model
{
    protected $table = 'inv_productos';

    protected $fillable = ['core_empresa_id', 'descripcion', 'tipo', 'unidad_medida1', 'unidad_medida2', 'categoria_id', 'inv_grupo_id', 'impuesto_id', 'precio_compra', 'precio_venta', 'estado', 'referencia', 'codigo_barras', 'imagen', 'mostrar_en_pagina_web', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'UM-1', 'Grupo inventario', 'Precio compra', 'Precio venta', 'IVA', 'Tipo', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_productos.tipo', 'servicio')
            ->select(
                'inv_productos.id AS campo1',
                'inv_productos.descripcion AS campo2',
                'inv_productos.unidad_medida1 AS campo3',
                'inv_grupos.descripcion AS campo4',
                'inv_productos.precio_compra AS campo5',
                'inv_productos.precio_venta AS campo6',
                'contab_impuestos.tasa_impuesto AS campo7',
                'inv_productos.tipo AS campo8',
                'inv_productos.estado AS campo9',
                'inv_productos.id AS campo10'
            )
            ->where("inv_productos.id", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.unidad_medida1", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_compra", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_venta", "LIKE", "%$search%")
            ->orWhere("contab_impuestos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("inv_productos.tipo", "LIKE", "%$search%")
            ->orWhere("inv_productos.estado", "LIKE", "%$search%")
            ->orderBy('inv_productos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_productos.tipo', 'servicio')
            ->select(
                'inv_productos.id AS CÓDIGO',
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
        $opciones = InvProducto::where('estado', 'Activo')
            ->where('tipo', 'servicio')
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->get();
        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion;
        }

        return $vec;
    }
}
