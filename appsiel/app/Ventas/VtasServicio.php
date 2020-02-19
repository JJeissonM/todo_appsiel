<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class VtasServicio extends Model
{
    //protected $table = 'inv_productos'; 

    protected $fillable = ['descripcion','precio_venta','estado'];

    public $encabezado_tabla = ['Código','Descripción','Precio','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = VtasServicio::select('vtas_servicios.id AS campo1','vtas_servicios.descripcion AS campo2','vtas_servicios.precio_venta AS campo3','vtas_servicios.estado AS campo4','vtas_servicios.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }

    
}
