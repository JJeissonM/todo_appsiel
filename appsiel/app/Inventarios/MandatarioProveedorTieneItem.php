<?php

namespace App\Inventarios;

use App\Compras\Proveedor;
use Illuminate\Database\Eloquent\Model;

use App\Sistema\Services\FieldsList;

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
}
