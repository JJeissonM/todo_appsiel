<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;

use App\Inventarios\InvGrupo;

use App\Contabilidad\Impuesto;


class InvProductoColegio extends Model
{
    protected $table = 'inv_productos'; 

    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Grado', 'Asignatura', 'Descripción', 'Editorial', 'Código barras', 'Cantidad', 'Estado'];


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

        $registros = InvProductoColegio::leftJoin('sga_grados', 'sga_grados.id', '=', 'inv_productos.categoria_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'inv_productos.impuesto_id')
            ->where($array_wheres)
            ->select(
                'inv_productos.id AS campo1',
                'sga_grados.descripcion AS campo2',
                'sga_asignaturas.descripcion AS campo3',
                'inv_productos.descripcion AS campo4',
                'inv_productos.unidad_medida2 AS campo5',
                'inv_productos.codigo_barras AS campo6',
                'inv_productos.referencia AS campo7',
                'inv_productos.estado AS campo8',
                'inv_productos.id AS campo9'
            )
            ->where("inv_productos.id", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.unidad_medida2", "LIKE", "%$search%")
            ->orWhere("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.codigo_barras", "LIKE", "%$search%")
            ->orWhere("inv_productos.referencia", "LIKE", "%$search%")
            ->orWhere("inv_productos.estado", "LIKE", "%$search%")
            ->orderBy('inv_productos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
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
                'inv_productos.id AS CÓDIGO',
                'sga_grados.descripcion AS GRADO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'inv_productos.descripcion AS DESCRIPCIÓN',
                'inv_productos.unidad_medida2 AS EDITORIAL',
                'inv_grupos.descripcion AS CATEGORIA',
                'inv_productos.codigo_barras AS CÓDIGO_BARRAS',
                'inv_productos.referencia AS CANTIDAD',
                'inv_productos.estado AS ESTADO'
            )
            ->where("inv_productos.id", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.unidad_medida2", "LIKE", "%$search%")
            ->orWhere("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.codigo_barras", "LIKE", "%$search%")
            ->orWhere("inv_productos.referencia", "LIKE", "%$search%")
            ->orWhere("inv_productos.estado", "LIKE", "%$search%")
            ->orderBy('inv_productos.created_at', 'DESC')
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
