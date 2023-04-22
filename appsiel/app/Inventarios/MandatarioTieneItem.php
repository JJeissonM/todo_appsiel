<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Services\FieldsList;

class MandatarioTieneItem extends Model
{
    protected $table = 'inv_mandatario_tiene_items'; 

    protected $fillable = [ 'mandatario_id', 'item_id' ];

    public function item_relacionado()
    {
        return $this->belongsTo(InvProducto::class, 'item_id');
    }

    public function get_fields_to_show()
    {
        $fields_list = new FieldsList( $this->crud_model_id, $this );
        return $fields_list->get_list_to_show();
    }

    public function store_adicional( $datos, $registro )
    {
        dd($datos, $registro);

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
