<?php

namespace App\Compras;

use App\Inventarios\InvProducto;
use Illuminate\Database\Eloquent\Model;

class ComprasPivotItemXml extends Model
{
    protected $table = 'compras_pivot_items_xml';

    protected $fillable = [
        'inv_producto_id',
        'codigo_item_xml',
        'nombre_item_xml',
        'proveedor_id',
    ];

    public function producto()
    {
        return $this->belongsTo(InvProducto::class, 'inv_producto_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
