<?php

namespace App\Ventas;

use App\Inventarios\InvBodega;
use App\Inventarios\InvGrupo;
use Illuminate\Database\Eloquent\Model;

class RestauranteCocina extends Model
{
    protected $table = 'vtas_restaurante_cocinas';

    const RUTA_STORAGE_IMAGEN = 'ventas/restaurante/cocinas/';

    protected $fillable = [
        'label',
        'grupo_inventarios_id',
        'bodega_default_id',
        'url_imagen',
        'printer_ip',
        'estado'
    ];

    public $encabezado_tabla = [
        '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>',
        'Cocina',
        'Grupo inventario',
        'Bodega default',
        'Imagen',
        'Printer IP',
        'Estado'
    ];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

    public static function activas()
    {
        return RestauranteCocina::where('estado', 'Activo')
            ->orderBy('label')
            ->get();
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return RestauranteCocina::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'vtas_restaurante_cocinas.grupo_inventarios_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'vtas_restaurante_cocinas.bodega_default_id')
            ->select(
                'vtas_restaurante_cocinas.label AS campo1',
                'inv_grupos.descripcion AS campo2',
                'inv_bodegas.descripcion AS campo3',
                'vtas_restaurante_cocinas.url_imagen AS campo4',
                'vtas_restaurante_cocinas.printer_ip AS campo5',
                'vtas_restaurante_cocinas.estado AS campo6',
                'vtas_restaurante_cocinas.id AS campo7'
            )
            ->where('vtas_restaurante_cocinas.label', 'LIKE', "%$search%")
            ->orWhere('inv_grupos.descripcion', 'LIKE', "%$search%")
            ->orWhere('inv_bodegas.descripcion', 'LIKE', "%$search%")
            ->orWhere('vtas_restaurante_cocinas.url_imagen', 'LIKE', "%$search%")
            ->orWhere('vtas_restaurante_cocinas.printer_ip', 'LIKE', "%$search%")
            ->orWhere('vtas_restaurante_cocinas.estado', 'LIKE', "%$search%")
            ->orderBy('vtas_restaurante_cocinas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = RestauranteCocina::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'vtas_restaurante_cocinas.grupo_inventarios_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'vtas_restaurante_cocinas.bodega_default_id')
            ->select(
                'vtas_restaurante_cocinas.label AS COCINA',
                'inv_grupos.descripcion AS GRUPO_INVENTARIO',
                'inv_bodegas.descripcion AS BODEGA_DEFAULT',
                'vtas_restaurante_cocinas.url_imagen AS URL_IMAGEN',
                'vtas_restaurante_cocinas.printer_ip AS PRINTER_IP',
                'vtas_restaurante_cocinas.estado AS ESTADO'
            )
            ->where('vtas_restaurante_cocinas.label', 'LIKE', "%$search%")
            ->orWhere('inv_grupos.descripcion', 'LIKE', "%$search%")
            ->orWhere('inv_bodegas.descripcion', 'LIKE', "%$search%")
            ->orWhere('vtas_restaurante_cocinas.url_imagen', 'LIKE', "%$search%")
            ->orWhere('vtas_restaurante_cocinas.printer_ip', 'LIKE', "%$search%")
            ->orWhere('vtas_restaurante_cocinas.estado', 'LIKE', "%$search%")
            ->orderBy('vtas_restaurante_cocinas.created_at', 'DESC')
            ->toSql();

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    public static function tituloExport()
    {
        return 'LISTADO DE COCINAS RESTAURANTE';
    }

    public static function opciones_campo_select()
    {
        $opciones = RestauranteCocina::activas();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->label;
        }

        return $vec;
    }

    public function get_url_imagen()
    {
        if ($this->url_imagen == '') {
            return '';
        }

        if (strpos($this->url_imagen, 'http://') === 0 || strpos($this->url_imagen, 'https://') === 0) {
            return $this->url_imagen;
        }

        return asset('appsiel/storage/app/' . self::RUTA_STORAGE_IMAGEN . $this->url_imagen);
    }

    public function show_adicional($lista_campos, $registro)
    {
        foreach ($lista_campos as $key => $campo) {
            if ($campo['name'] != 'url_imagen') {
                continue;
            }

            $urlImagen = $registro->get_url_imagen();
            $lista_campos[$key]['value'] = '';
            if ($urlImagen != '') {
                $lista_campos[$key]['value'] = '<img alt="imagen.jpg" src="' . $urlImagen . '" style="width: auto; height: 160px;" />';
            }
        }

        return $lista_campos;
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        foreach ($lista_campos as $key => $campo) {
            if ($campo['name'] == 'url_imagen') {
                $lista_campos[$key]['value'] = $registro->get_url_imagen();
            }
        }

        return $lista_campos;
    }

    public function grupo_inventario()
    {
        return $this->belongsTo(InvGrupo::class, 'grupo_inventarios_id');
    }

    public function bodega_default()
    {
        return $this->belongsTo(InvBodega::class, 'bodega_default_id');
    }
}
