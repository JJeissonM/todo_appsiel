<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class VtasServicio extends Model
{
    //protected $table = 'inv_productos'; 

    protected $fillable = ['descripcion', 'precio_venta', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Precio', 'Estado'];

    public static function consultar_registros($nro_registros)
    {
        $registros = VtasServicio::select('vtas_servicios.id AS campo1', 'vtas_servicios.descripcion AS campo2', 'vtas_servicios.precio_venta AS campo3', 'vtas_servicios.estado AS campo4', 'vtas_servicios.id AS campo5')
            ->orderBy('vtas_servicios.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }
}
