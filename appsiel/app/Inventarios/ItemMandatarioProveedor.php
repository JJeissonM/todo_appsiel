<?php

namespace App\Inventarios;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemMandatarioProveedor extends ItemMandatario
{
    protected $table = 'inv_items_mandatarios';

    protected $fillable = [ 'core_empresa_id', 'descripcion', 'unidad_medida1', 'referencia', 'inv_grupo_id', 'impuesto_id', 'paleta_color_id', 'prefijo_referencia_id', 'tipo_material_id', 'tipo_prenda_id', 'estado', 'creado_por', 'modificado_por' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Ref.', 'Descripción', 'Grupo', 'IVA %', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"inv_item_mandatario/id_fila","store":"web","update":"web/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $array_wheres = [
                            ['inv_items_mandatarios.core_empresa_id', Auth::user()->empresa_id]
                        ];

        return ItemMandatarioProveedor::leftJoin('inv_grupos','inv_grupos.id','=','inv_items_mandatarios.inv_grupo_id')
        ->leftJoin('contab_impuestos','contab_impuestos.id','=','inv_items_mandatarios.impuesto_id')
        ->where($array_wheres)
            ->select(
                'inv_items_mandatarios.referencia AS campo1',
                'inv_items_mandatarios.descripcion AS campo2',
                DB::raw('inv_grupos.descripcion AS campo3'),
                DB::raw('contab_impuestos.descripcion AS campo4'),
                'inv_items_mandatarios.estado AS campo5',
                'inv_items_mandatarios.id AS campo6'
            )
            ->where("inv_items_mandatarios.id", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.referencia", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_impuestos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.estado", "LIKE", "%$search%")
            ->orderBy('inv_items_mandatarios.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $array_wheres = [
            ['inv_items_mandatarios.core_empresa_id', Auth::user()->empresa_id]
        ];

        $string = ItemMandatarioProveedor::leftJoin('inv_grupos','inv_grupos.id','=','inv_items_mandatarios.inv_grupo_id')
        ->leftJoin('contab_impuestos','contab_impuestos.id','=','inv_items_mandatarios.impuesto_id')
        ->where($array_wheres)
            ->select(
                'inv_items_mandatarios.id AS CÓDIGO',
                'inv_items_mandatarios.referencia AS REFERENCIA',
                'inv_items_mandatarios.descripcion AS DESCRIPCIÓN',
                DB::raw('inv_grupos.descripcion AS GRUPO'),
                DB::raw('contab_impuestos.descripcion AS IVA'),
                'inv_items_mandatarios.estado AS ESTADO'
            )
            ->where("inv_items_mandatarios.id", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.referencia", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_impuestos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.estado", "LIKE", "%$search%")
            ->orderBy('inv_items_mandatarios.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PRENDAS";
    }    

    public static function opciones_campo_select()
    {
        $opciones = ItemMandatarioProveedor::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->referencia . ' ' . $opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional( $datos, $registro )
    {
        $referencia = $this->build_reference($datos, $registro);

        $user = Auth::user();
        $registro->referencia = $referencia;
        $registro->estado = 'Activo';
        $registro->core_empresa_id = $user->empresa_id;
        $registro->save();
    }

    public function update_adicional($datos, $id)
    {
        $prenda = ItemMandatarioProveedor::find( $id );
        
        $referencia = $this->build_reference($datos, $prenda);

        $prenda->referencia = $referencia;
        $prenda->save();

        $registros_relacionados = $prenda->items_relacionados;
        
        foreach ($registros_relacionados as $item_relacionado )
        {
            $item_relacionado->descripcion = $prenda->descripcion;
            $item_relacionado->referencia = $referencia . '-' . $item_relacionado->unidad_medida2;
            $item_relacionado->save();
        }
    }

    public function build_reference( $datos, $registro )
    {
        if (isset($dato['referencia'])) {
            return $dato['referencia'];
        }

        $reference = '';

        if ( $registro->prefijo_referencia != null ) {
            $reference .= $registro->prefijo_referencia->codigo;
        }
        

        if ( $registro->tipo_prenda != null ) {
            $reference .= $registro->tipo_prenda->codigo;
        }
        

        if ( $registro->paleta_color != null ) {
            $reference .= $registro->paleta_color->codigo;
        }
        

        if ( $registro->tipo_material != null ) {
            $reference .= $registro->tipo_material->codigo;
        }
        
        return $reference;
    }
}