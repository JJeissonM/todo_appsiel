<?php

namespace App\Inventarios;

use App\Compras\Proveedor;
use Illuminate\Database\Eloquent\Model;

use App\Sistema\Services\FieldsList;

use App\Ventas\Services\PricesServices;
use Illuminate\Support\Facades\Auth;

// ID = 
class MandatarioProveedorTieneItem extends Model
{
    protected $table = 'inv_mandatario_tiene_items'; 

    protected $fillable = [ 'mandatario_id', 'item_id' ];

    public function item_relacionado()
    {
        return $this->belongsTo(InvProducto::class, 'item_id');
    }

    public function proveedor()
    {
        return Proveedor::find( (int)$this->item_relacionado->categoria_id );
    }

    public function get_fields_to_show()
    {
        $fields_list = new FieldsList( $this->crud_model_id, $this );
        return $fields_list->get_list_to_show();
    }

    public function store_adicional( $datos, $registro )
    {
        $user = Auth::user();
        $registro->estado = 'Activo';
        $registro->core_empresa_id = $user->empresa_id;
        $registro->save();
    }

    public function update_adicional($datos, $id)
    {        
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $datos['fecha_activacion'] = date('Y-m-d');
            $datos['inv_producto_id'] = $id;
            $datos['lista_precios_id'] = (int)config('ventas.lista_precios_id');
            
            (new PricesServices())->create_or_update_item_price( $datos );
        }
    }
}
