<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $table = 'pw_tiendas';
    protected $fillable = ['id', 'ubicacion', 'direccion1', 'direccion2', 'ciudad', 'pais', 'codigo_postal', 'activarimpuesto', 'comportamiento_carrito','unidad_peso', 'unidad_dimensiones', 'aviso_poca_exitencia', 'aviso_inventario_agotado', 'email_destinatario','umbral_existencia', 'umbral_inventario_agotado', 'visibilidad_inv_agotado', 'mostrar_inventario', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
