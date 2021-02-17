<?php

namespace App\Sistema;

use Illuminate\Database\Eloquent\Model;

class Aplicacion extends Model
{
    protected $table = 'sys_aplicaciones';

    protected $fillable = ['ambito', 'descripcion', 'app', 'definicion', 'tipo_precio', 'precio', 'orden', 'nombre_imagen', 'mostrar_en_pag_web', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Ámbito', 'Descripción', 'Detalles', 'Tipo precio', 'Precio', 'Orden', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Aplicacion::select(
            'sys_aplicaciones.ambito AS campo1',
            'sys_aplicaciones.descripcion AS campo2',
            'sys_aplicaciones.definicion AS campo3',
            'sys_aplicaciones.tipo_precio AS campo4',
            'sys_aplicaciones.precio AS campo5',
            'sys_aplicaciones.orden AS campo6',
            'sys_aplicaciones.estado AS campo7',
            'sys_aplicaciones.id AS campo8'
        )
            ->where("sys_aplicaciones.ambito", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.definicion", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.tipo_precio", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.precio", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.orden", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.estado", "LIKE", "%$search%")
            ->orderBy('sys_aplicaciones.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Aplicacion::select(
            'sys_aplicaciones.ambito AS AMBITO',
            'sys_aplicaciones.descripcion AS DESCRIPCIÓN',
            'sys_aplicaciones.definicion AS DEFINICIÓN',
            'sys_aplicaciones.tipo_precio AS TIPO_APLICACIÓN',
            'sys_aplicaciones.precio AS PRECIO',
            'sys_aplicaciones.orden AS ORDEN',
            'sys_aplicaciones.estado AS ESTADO'
        )
            ->where("sys_aplicaciones.ambito", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.definicion", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.tipo_precio", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.precio", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.orden", "LIKE", "%$search%")
            ->orWhere("sys_aplicaciones.estado", "LIKE", "%$search%")
            ->orderBy('sys_aplicaciones.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE APLICACIONES DEL SISTEMA";
    }

    public function tipos_transacciones()
    {
        return $this->hasMany('App\Sistema\TipoTransaccion', 'core_app_id');
    }

    public static function opciones_campo_select()
    {
        $opciones = Aplicacion::all();//where('estado', '=', 'Activo')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
