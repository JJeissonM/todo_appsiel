<?php

namespace App\Inventarios;

use App\Inventarios\Services\ItemsMandatariosSerices;
use Illuminate\Database\Eloquent\Model;

use App\Sistema\Services\FieldsList;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\Services\PricesServices;
use Illuminate\Support\Facades\Auth;

class MandatarioTieneItem extends Model
{
    protected $table = 'inv_mandatario_tiene_items'; 

    protected $fillable = [ 'mandatario_id', 'item_id' ];

    public function item_relacionado()
    {
        return $this->belongsTo(InvProducto::class, 'item_id');
    }

    public function item_mandatario()
    {
        return $this->belongsTo(ItemMandatario::class, 'mandatario_id');
    }

    public function get_fields_to_show()
    {
        $fields_list = new FieldsList( $this->crud_model_id, $this );
        return $fields_list->get_list_to_show();
    }

    public function store_adicional( $datos, $registro )
    {
        
        $referencia = (new ItemsMandatariosSerices())->build_reference($datos, $registro);

        $user = Auth::user();
        $registro->referencia = $referencia;
        $registro->estado = 'Activo';
        $registro->core_empresa_id = $user->empresa_id;
        $registro->save();
    }

    public function update_adicional($datos, $id)
    {
        $prenda = ItemMandatario::find( $id );
        
        $referencia = (new ItemsMandatariosSerices())->build_reference($datos, $prenda);

        $prenda->referencia = $referencia;
        $prenda->save();

        $registros_relacionados = $prenda->items_relacionados;
        
        foreach ($registros_relacionados as $item_relacionado )
        {
            $item_relacionado->descripcion = $prenda->descripcion;
            $item_relacionado->unidad_medida2 = $referencia . '-' . $item_relacionado->unidad_medida2;
            $item_relacionado->save();
        }
        
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $datos['fecha_activacion'] = date('Y-m-d');
            $datos['inv_producto_id'] = $id;
            $datos['lista_precios_id'] = (int)config('ventas.lista_precios_id');

            (new PricesServices())->create_or_update_item_price( $datos );
        }
    }
}
