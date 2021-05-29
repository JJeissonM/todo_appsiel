<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class DireccionEntrega extends Model
{
    protected $table = 'vtas_direcciones_entrega_clientes';
    
    protected $fillable = ['cliente_id', 'nombre_contacto', 'codigo_ciudad', 'direccion1', 'barrio', 'codigo_postal', 'telefono1', 'datos_adicionales', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Cliente', 'Nombre', 'Ciudad', 'Dirección', 'Barrio', 'Cód. postal', 'Teléfono', 'Datos adicionales', 'Estado'];

    public function cliente()
    {
        return $this->belongsTo( Cliente::class, 'cliente_id' );
    }

    public function ciudad()
    {
        return $this->belongsTo('App\Core\Ciudad', 'codigo_ciudad');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return DireccionEntrega::select('vtas_direcciones_entrega_clientes.cliente_id AS campo1', 'vtas_direcciones_entrega_clientes.nombre_contacto AS campo2', 'vtas_direcciones_entrega_clientes.codigo_ciudad AS campo3', 'vtas_direcciones_entrega_clientes.direccion1 AS campo4', 'vtas_direcciones_entrega_clientes.barrio AS campo5', 'vtas_direcciones_entrega_clientes.codigo_postal AS campo6', 'vtas_direcciones_entrega_clientes.telefono1 AS campo7', 'vtas_direcciones_entrega_clientes.datos_adicionales AS campo8', 'vtas_direcciones_entrega_clientes.estado AS campo9', 'vtas_direcciones_entrega_clientes.id AS campo10')
        ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $string = DireccionEntrega::select('vtas_direcciones_entrega_clientes.cliente_id AS campo1', 'vtas_direcciones_entrega_clientes.nombre_contacto AS campo2', 'vtas_direcciones_entrega_clientes.codigo_ciudad AS campo3', 'vtas_direcciones_entrega_clientes.direccion1 AS campo4', 'vtas_direcciones_entrega_clientes.barrio AS campo5', 'vtas_direcciones_entrega_clientes.codigo_postal AS campo6', 'vtas_direcciones_entrega_clientes.telefono1 AS campo7', 'vtas_direcciones_entrega_clientes.datos_adicionales AS campo8', 'vtas_direcciones_entrega_clientes.estado AS campo9', 'vtas_direcciones_entrega_clientes.id AS campo10')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE DIRECCIONES DE ENTREGA DE CLIENTES";
    }

    public static function opciones_campo_select()
    {
        $opciones = DireccionEntrega::where('vtas_direcciones_entrega_clientes.estado','Activo')
                    ->select('vtas_direcciones_entrega_clientes.id','vtas_direcciones_entrega_clientes.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
