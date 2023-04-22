<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use App\Inventarios\Indumentaria\PaletaColores;
use App\Inventarios\Indumentaria\PrefijoReferencia;
use App\Inventarios\Indumentaria\TipoMaterial;
use App\Inventarios\Indumentaria\TipoPrenda;
use Illuminate\Support\Facades\Auth;

//  PRENDAS
class ItemMandatario extends Model
{
    protected $table = 'inv_items_mandatarios';

    protected $fillable = [ 'core_empresa_id', 'descripcion', 'referencia', 'inv_grupo_id', 'paleta_color_id', 'prefijo_referencia_id', 'tipo_material_id', 'tipo_prenda_id', 'estado', 'creado_por', 'modificado_por' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Ref.', 'Descripción', 'Tipo', 'Material', 'Color', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"inv_item_mandatario/id_fila","store":"web","update":"web/id_fila"}';

    public function grupo_inventario()
    {
        return $this->belongsTo(InvGrupo::class, 'inv_grupo_id');
    }

    public function paleta_color()
    {
        return $this->belongsTo(PaletaColores::class, 'paleta_color_id');
    }

    public function prefijo_referencia()
    {
        return $this->belongsTo(PrefijoReferencia::class, 'prefijo_referencia_id');
    }

    public function tipo_material()
    {
        return $this->belongsTo(TipoMaterial::class, 'tipo_material_id');
    }

    public function tipo_prenda()
    {
        return $this->belongsTo(TipoPrenda::class, 'tipo_prenda_id');
    }

    public function items_relacionados()
    {
        return $this->belongsToMany( InvProducto::class, 'inv_mandatario_tiene_items', 'mandatario_id', 'item_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $array_wheres = [
                            ['inv_items_mandatarios.core_empresa_id', Auth::user()->empresa_id]
                        ];

        return ItemMandatario::where($array_wheres)
            ->select(
                'inv_items_mandatarios.referencia AS campo1',
                'inv_items_mandatarios.descripcion AS campo2',
                'inv_items_mandatarios.tipo_prenda_id AS campo3',
                'inv_items_mandatarios.tipo_material_id AS campo4',
                'inv_items_mandatarios.paleta_color_id AS campo5',
                'inv_items_mandatarios.estado AS campo6',
                'inv_items_mandatarios.id AS campo7'
            )
            ->where("inv_items_mandatarios.id", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.descripcion", "LIKE", "%$search%")
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

        $string = ItemMandatario::where($array_wheres)
            ->select(
                'inv_items_mandatarios.id AS CÓDIGO',
                'inv_items_mandatarios.descripcion AS DESCRIPCIÓN',
                'inv_items_mandatarios.estado AS ESTADO'
            )
            ->where("inv_items_mandatarios.id", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.descripcion", "LIKE", "%$search%")
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
        $opciones = ItemMandatario::where('estado','Activo')
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
        $referencia = $registro->prefijo_referencia->codigo . $registro->tipo_prenda->codigo . $registro->paleta_color->codigo . $registro->tipo_material->codigo;

        $user = Auth::user();
        $registro->referencia = $referencia;
        $registro->estado = 'Activo';
        $registro->core_empresa_id = $user->empresa_id;
        $registro->save();
    }

    public function update_adicional($datos, $id)
    {
        $prenda = ItemMandatario::find( $id );
        
        $referencia = $prenda->prefijo_referencia->codigo . $prenda->tipo_prenda->codigo . $prenda->paleta_color->codigo . $prenda->tipo_material->codigo;

        $prenda->referencia = $referencia;
        $prenda->save();

        $registros_relacionados = $prenda->items_relacionados;
        
        foreach ($registros_relacionados as $item_relacionado )
        {
            $item_relacionado->descripcion = $prenda->descripcion;
            $item_relacionado->unidad_medida2 = $referencia . '-' . $item_relacionado->unidad_medida2;
            $item_relacionado->save();
        }
    }
}