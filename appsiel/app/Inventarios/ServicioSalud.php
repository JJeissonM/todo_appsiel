<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;

use App\Inventarios\InvGrupo;

use App\Contabilidad\Impuesto;


class ServicioSalud extends InvProducto
{
    protected $table = 'inv_productos'; 

    /*
        referencia = COD. CUPS
        codigo_barras = COD. SOAT
    */
    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por'];

    public $encabezado_tabla = [ 'ID', 'Descripción', 'Código CUPS', 'Código SOAT', 'Grupo servicio', 'Estado', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros()
    {
        return ServicioSalud::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                    ->select(
                                'inv_productos.id AS campo1',
                                'inv_productos.descripcion AS campo2',
                                'inv_productos.referencia AS campo3',
                                'inv_productos.codigo_barras AS campo4',
                                'inv_grupos.descripcion AS campo5',
                                'inv_productos.estado AS campo6',
                                'inv_productos.id AS campo7')
                    ->get()
                    ->toArray();
    }
    

    public static function opciones_campo_select()
    {
        $opciones = ServicioSalud::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion)
        {   
            $codigo_cups = '';
            $codigo_soat = '';

            if ( $opcion->codigo_barras != '' )
            {
                $codigo_soat = ' - Cod. SOAT: ' . $opcion->codigo_barras;
            }

            if ( $opcion->referencia != '' )
            {
                $codigo_soat = ' - Cod. CUPS: ' . $opcion->referencia;
            }

            $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion . $codigo_soat . $codigo_cups;
        }

        return $vec;
    }
}
