<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;

use App\Inventarios\InvGrupo;
use App\Inventarios\MandatarioTieneItem;

use App\Inventarios\Services\CodigoBarras;

use App\Contabilidad\Impuesto;


class ItemMandatario extends Model
{
    protected $table = 'inv_items_mandatarios'; 

    protected $fillable = [ 'core_empresa_id', 'descripcion', 'tipo', 'unidad_medida1', 'unidad_medida2', 'inv_grupo_id', 'impuesto_id', 'precio_compra', 'precio_venta', 'estado', 'imagen', 'creado_por', 'modificado_por' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Grupo inventario', 'Descripción', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"inv_item_mandatario/id_fila","store":"web","update":"web/id_fila"}';

    public function grupo_inventario()
    {
        return $this->belongsTo(InvGrupo::class, 'inv_grupo_id');
    }

    public function items_relacionados()
    {
        return $this->belongsToMany( InvProducto::class, 'inv_mandatario_tiene_items', 'mandatario_id', 'item_id');
    }

    public function get_costo_promedio( $bodega_id )
    {
        $costo_prom = InvCostoPromProducto::where([
                                                    ['inv_bodega_id','=',$bodega_id],
                                                    ['inv_producto_id','=', $this->id]
                                                ])
                                        ->value('costo_promedio');

        if ( is_null( $costo_prom ) || $costo_prom <= 0 )
        {
            $costo_prom = $this->precio_compra;
        }

        if ( $costo_prom <= 0 )
        {
            $costo_prom = 1;
        }

        return $costo_prom;
    }

    public function set_costo_promedio( $bodega_id, $costo_prom )
    {
        $registro_costo_prom = InvCostoPromProducto::where([
                                                    ['inv_bodega_id','=',$bodega_id],
                                                    ['inv_producto_id','=', $this->id]
                                                ])
                                        ->get()
                                        ->first();

        if ( is_null( $registro_costo_prom ) )
        {
            $registro_costo_prom = new InvCostoPromProducto();
            $registro_costo_prom->inv_bodega_id = $bodega_id;
            $registro_costo_prom->inv_producto_id = $this->id;
            $registro_costo_prom->costo_promedio = $costo_prom;
            $registro_costo_prom->save();
        }else{
            $registro_costo_prom->costo_promedio = $costo_prom;
            $registro_costo_prom->save();
        }
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $array_wheres = [
                            ['inv_items_mandatarios.core_empresa_id', Auth::user()->empresa_id]
                        ];

        return ItemMandatario::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_items_mandatarios.inv_grupo_id')
            ->where($array_wheres)
            ->select(
                'inv_items_mandatarios.id AS campo1',
                'inv_grupos.descripcion AS campo2',
                'inv_items_mandatarios.descripcion AS campo3',
                'inv_items_mandatarios.estado AS campo4',
                'inv_items_mandatarios.id AS campo5'
            )
            ->where("inv_items_mandatarios.id", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.estado", "LIKE", "%$search%")
            ->orderBy('inv_items_mandatarios.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $array_wheres = [
            ['inv_items_mandatarios.core_empresa_id', Auth::user()->empresa_id]
        ];

        $string = ItemMandatario::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_items_mandatarios.inv_grupo_id')
            ->where($array_wheres)
            ->select(
                'inv_items_mandatarios.id AS CÓDIGO',
                'inv_grupos.descripcion AS GRUPO_INVENTARIO',
                'inv_items_mandatarios.descripcion AS DESCRIPCIÓN',
                'inv_items_mandatarios.estado AS ESTADO'
            )
            ->where("inv_items_mandatarios.id", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.estado", "LIKE", "%$search%")
            ->orderBy('inv_items_mandatarios.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PRODUCTOS";
    }
    

    public static function opciones_campo_select()
    {
        $opciones = ItemMandatario::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional( $datos, $registro )
    {
        $user = Auth::user();
        $registro->estado = 'Activo';
        $registro->tipo = 'producto';
        $registro->impuesto_id = (int)config('inventarios.item_impuesto_id');
        $registro->core_empresa_id = $user->empresa_id;
        $registro->unidad_medida1 = 'UND';
        $registro->save();
    }

    public function update_adicional($datos, $id)
    {
        $mandatario = ItemMandatario::find( $id );
        $registros_relacionados = $mandatario->items_relacionados;

        foreach ($registros_relacionados as $registro )
        {
            $registro->item_relacionado->descripcion = $mandatario->descripcion;
            $registro->item_relacionado->inv_grupo_id = $mandatario->inv_grupo_id;
            $registro->item_relacionado->precio_compra = $mandatario->precio_compra;
            $registro->item_relacionado->precio_venta = $mandatario->precio_venta;
            $registro->item_relacionado->save();

            $registro->item_relacionado->set_costo_promedio( (int)config('inventarios.item_bodega_principal_id'), $mandatario->precio_compra );
        }
    }
}