<?php

namespace App\Inventarios;

use App\Contabilidad\Impuesto;
use Illuminate\Database\Eloquent\Model;

use App\Inventarios\Indumentaria\PaletaColores;
use App\Inventarios\Indumentaria\PrefijoReferencia;
use App\Inventarios\Indumentaria\TipoMaterial;
use App\Inventarios\Indumentaria\TipoPrenda;
use App\Inventarios\Services\ItemsMandatariosSerices;
use App\Sistema\Campo;
use App\Sistema\Services\CrudService;
use App\Ventas\ListaPrecioDetalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//  PRENDAS
class ItemMandatario extends Model
{
    protected $table = 'inv_items_mandatarios';

    protected $fillable = [ 'core_empresa_id', 'descripcion', 'unidad_medida1', 'referencia', 'imagen', 'inv_grupo_id', 'impuesto_id', 'paleta_color_id', 'prefijo_referencia_id', 'tipo_material_id', 'tipo_prenda_id', 'estado', 'creado_por', 'modificado_por' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Ref.', 'Descripción', 'Color', 'Material', 'Tipo', 'Categoría', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"inv_item_mandatario/id_fila","store":"web","update":"web/id_fila","eliminar":"web_eliminar/id_fila"}';

    public $archivo_js = 'assets/js/inventarios/crud_prendas.js';    

    public function get_unidad_medida1()
    {
        $campo = Campo::find(79);
        $opciones = json_decode($campo->opciones, true);

        return $opciones[$this->unidad_medida1] ?? $this->unidad_medida1;
    }

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

    public function impuesto()
    {
        return $this->belongsTo(Impuesto::class, 'impuesto_id');
    }

    public function items_relacionados()
    {
        return $this->belongsToMany( InvProducto::class, 'inv_mandatario_tiene_items', 'mandatario_id', 'item_id');
    }

    public function get_impuesto_label()
    {
        if ( $this->impuesto == null ) {
            return '';
        }

        return $this->impuesto->tasa_impuesto . '%';
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $array_wheres = [
                            ['inv_items_mandatarios.core_empresa_id', Auth::user()->empresa_id]
                        ];

        return ItemMandatario::leftJoin('inv_indum_tipos_prendas','inv_indum_tipos_prendas.id','=','inv_items_mandatarios.tipo_prenda_id')
        ->leftJoin('inv_indum_tipos_materiales','inv_indum_tipos_materiales.id','=','inv_items_mandatarios.tipo_material_id')
        ->leftJoin('inv_indum_paletas_colores','inv_indum_paletas_colores.id','=','inv_items_mandatarios.paleta_color_id')
        ->leftJoin( 'inv_grupos', 'inv_grupos.id', '=', 'inv_items_mandatarios.inv_grupo_id')
        ->where($array_wheres)
            ->select(
                'inv_items_mandatarios.referencia AS campo1',
                'inv_items_mandatarios.descripcion AS campo2',
                DB::raw('CONCAT(inv_indum_paletas_colores.descripcion," (",inv_indum_paletas_colores.codigo,") ") AS campo3'),
                DB::raw('CONCAT(inv_indum_tipos_materiales.descripcion," (",inv_indum_tipos_materiales.codigo,") ") AS campo4'),
                DB::raw('CONCAT(inv_indum_tipos_prendas.descripcion," (",inv_indum_tipos_prendas.codigo,") ") AS campo5'),
                'inv_grupos.descripcion AS campo6',
                'inv_items_mandatarios.estado AS campo7',
                'inv_items_mandatarios.id AS campo8'
            )
            ->where("inv_items_mandatarios.id", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.referencia", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_indum_tipos_prendas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_indum_tipos_materiales.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_indum_paletas_colores.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.estado", "LIKE", "%$search%")
            ->orderBy('inv_items_mandatarios.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $array_wheres = [
            ['inv_items_mandatarios.core_empresa_id', Auth::user()->empresa_id]
        ];

        $string = ItemMandatario::leftJoin('inv_indum_tipos_prendas','inv_indum_tipos_prendas.id','=','inv_items_mandatarios.tipo_prenda_id')
        ->leftJoin('inv_indum_tipos_materiales','inv_indum_tipos_materiales.id','=','inv_items_mandatarios.tipo_material_id')
        ->leftJoin('inv_indum_paletas_colores','inv_indum_paletas_colores.id','=','inv_items_mandatarios.paleta_color_id')
        ->leftJoin( 'inv_grupos', 'inv_grupos.id', '=', 'inv_items_mandatarios.inv_grupo_id')
        ->where($array_wheres)
            ->select(
                'inv_items_mandatarios.id AS CÓDIGO',
                'inv_items_mandatarios.referencia AS REFERENCIA',
                'inv_items_mandatarios.descripcion AS DESCRIPCIÓN',
                DB::raw('CONCAT(inv_indum_tipos_prendas.descripcion," (",inv_indum_tipos_prendas.codigo,") ") AS TIPO_PRENDA'),
                DB::raw('CONCAT(inv_indum_tipos_materiales.descripcion," (",inv_indum_tipos_materiales.codigo,") ") AS TIPO_MATERIAL'),
                DB::raw('CONCAT(inv_indum_paletas_colores.descripcion," (",inv_indum_paletas_colores.codigo,") ") AS COLOR'),
                'inv_grupos.descripcion AS CATEGORIA',
                'inv_items_mandatarios.estado AS ESTADO'
            )
            ->where("inv_items_mandatarios.id", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.referencia", "LIKE", "%$search%")
            ->orWhere("inv_items_mandatarios.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_indum_tipos_prendas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_indum_tipos_materiales.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_indum_paletas_colores.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
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
        $referencia = (new ItemsMandatariosSerices())->build_reference($datos, $registro);

        $user = Auth::user();
        $registro->referencia = $referencia;
        $registro->estado = 'Activo';
        $registro->core_empresa_id = $user->empresa_id;

        if ( !isset( $datos['impuesto_id'] ) ) {
            $registro->impuesto_id = (int)config('inventarios.item_impuesto_id');
        }

        if ( !isset( $datos['unidad_medida1'] ) ) {
            $registro->unidad_medida1 = 94; // Unidad de medida por defecto: UND
        }

        $registro->save();
    }

    public static function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            switch ($lista_campos[$i]['name']) {
                case 'lbl_descripcion_de_prenda':

                    $lista_campos[$i]['value'] = '<div style="border-style: outset; margin: 15px; padding: 10px; height: 40px; font-size: 1.2em;"> Descripción Prenda: <span id="lbl_descripcion"> ' . $registro->descripcion  . ' </span> </div> <input type="hidden" id="descripcion" name="descripcion" value="' . $registro->descripcion  . '" />';
                    
                    break;

                case 'descripcion_detalle':

                    $descripcion_detalle = str_replace( $registro->tipo_prenda->descripcion . ' ', '', $registro->descripcion );
                    
                    $descripcion_detalle = str_replace( ' ' . $registro->tipo_material->descripcion, '', $descripcion_detalle );

                    $lista_campos[$i]['value'] = $descripcion_detalle;
                    
                    break;

                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }

    public function update_adicional($datos, $id)
    {
        $prenda = ItemMandatario::find( $id );
        
        $referencia = (new ItemsMandatariosSerices())->build_reference($datos, $prenda);

        if ( !isset( $datos['impuesto_id'] ) ) {
            $prenda->impuesto_id = (int)config('inventarios.item_impuesto_id');
        }

        if ( !isset( $datos['unidad_medida1'] ) ) {
            $prenda->unidad_medida1 = 94; // Unidad de medida por defecto: UND
        }

        if ( $prenda->estado == null ) {
            $prenda->estado = 'Activo';
        }

        if ( $prenda->core_empresa_id == null ) {
            $prenda->core_empresa_id = Auth::user()->empresa_id;
        }

        $prenda->referencia = $referencia;
        $prenda->save();

        $registros_relacionados = $prenda->items_relacionados;
        
        foreach ($registros_relacionados as $item_relacionado )
        {
            $item_relacionado->unidad_medida1 = $prenda->unidad_medida1;
            $item_relacionado->inv_grupo_id = $prenda->inv_grupo_id;
            $item_relacionado->impuesto_id = $prenda->impuesto_id;
            $item_relacionado->prefijo_referencia_id = $prenda->prefijo_referencia_id;
            $item_relacionado->descripcion = $prenda->descripcion;
            $item_relacionado->referencia = $referencia . '-' . $item_relacionado->unidad_medida2;
            $item_relacionado->save();
        }
    }

    public function validar_eliminacion($id )
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"inv_mandatario_tiene_items",
                                    "llave_foranea":"mandatario_id",
                                    "mensaje":"Ítem mandatario tiene Ítems relacionados."
                                }
                        }';

        return (new CrudService())->validar_eliminacion_un_registro( $id, $tablas_relacionadas);
    }
}