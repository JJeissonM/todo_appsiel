<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Services\FieldsList;
use App\Ventas\ListaPrecioDetalle;
use Illuminate\Support\Facades\Auth;

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
        
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $nuevo_precio_venta = 0;
            if (isset($datos['precio_venta'])) {
                $nuevo_precio_venta = $datos['precio_venta'];
            }

            $reg_precio_actual = ListaPrecioDetalle::where([
                ['lista_precios_id', '=', (int)config('ventas.lista_precios_id')],
                ['inv_producto_id', '=', $id]
            ])
            ->get()
            ->last();

            if ($reg_precio_actual == null) {
                ListaPrecioDetalle::create([
                    'lista_precios_id' => (int)config('ventas.lista_precios_id'),
                    'inv_producto_id' => $id,
                    'fecha_activacion' => date('Y-m-d'),
                    'precio' => $nuevo_precio_venta
                ]);
            }else{
                if ($nuevo_precio_venta != $reg_precio_actual->precio) {
                    $reg_precio_actual->precio = $nuevo_precio_venta;
                    $reg_precio_actual->save();
                }
            }
        }
    }
}
