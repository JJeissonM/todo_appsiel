<?php

namespace App\Sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class Modelo extends Model
{
    //public $modelo; Advertencia!!! No usar este nombre de propiedad ($modelo), ya existe un columna con este nombre en la tabla sys_modelos

    protected $table = 'sys_modelos';

    // Quitar de la BD los campos: ruta_storage_archivo_adjunto
    // El campo ruta_storage_imagen almacenará todos los archivos tipo file que maneje el modelo (cambiar nombre del campo)
    protected $fillable = ['descripcion', 'modelo', 'name_space', 'modelo_relacionado', 'url_crear', 'url_edit', 'url_print', 'url_ver', 'enlaces', 'url_estado', 'controller_complementario', 'url_form_create', 'url_eliminar', 'home_miga_pan', 'ruta_storage_imagen'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'ID', 'Descripción', 'Ubicación', 'Modelo relacionado', 'Directorio Imágenes', 'Enlaces', 'Modelo (Name)', 'Show', 'Print', 'Eliminar'];

    public function campos()
    {
        return $this->belongsToMany('App\Sistema\Campo', 'sys_modelo_tiene_campos', 'core_modelo_id', 'core_campo_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Modelo::select(

            'sys_modelos.id AS campo1',
            'sys_modelos.descripcion AS campo2',
            'sys_modelos.name_space AS campo3',
            'sys_modelos.modelo_relacionado AS campo4',
            'sys_modelos.ruta_storage_imagen AS campo5',
            'sys_modelos.enlaces AS campo6',
            'sys_modelos.modelo AS campo7',
            'sys_modelos.url_ver AS campo8',
            'sys_modelos.url_print AS campo9',
            'sys_modelos.modelo AS campo10',
            'sys_modelos.id AS campo11'
        )
            ->where("sys_modelos.id", "LIKE", "%$search%")
            ->orWhere("sys_modelos.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_modelos.name_space", "LIKE", "%$search%")
            ->orWhere("sys_modelos.modelo_relacionado", "LIKE", "%$search%")
            ->orWhere("sys_modelos.ruta_storage_imagen", "LIKE", "%$search%")
            ->orWhere("sys_modelos.enlaces", "LIKE", "%$search%")
            ->orWhere("sys_modelos.modelo", "LIKE", "%$search%")
            ->orWhere("sys_modelos.url_ver", "LIKE", "%$search%")
            ->orWhere("sys_modelos.url_print", "LIKE", "%$search%")
            ->orWhere("sys_modelos.url_eliminar", "LIKE", "%$search%")
            ->orderBy('sys_modelos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Modelo::select(
            'sys_modelos.id AS ID',
            'sys_modelos.descripcion AS DESCRIPCIÓN',
            'sys_modelos.name_space AS UBICACIÓN',
            'sys_modelos.modelo_relacionado AS MODELO_RELACIONADO',
            'sys_modelos.ruta_storage_imagen AS DIRECTORIO_IMÁGENES',
            'sys_modelos.enlaces AS ENLACES',
            'sys_modelos.modelo AS MODELO(NAME)',
            'sys_modelos.url_ver AS SHOW',
            'sys_modelos.url_print AS PRINT',
            'sys_modelos.url_eliminar AS ELIMINAR'
        )
            ->where("sys_modelos.id", "LIKE", "%$search%")
            ->orWhere("sys_modelos.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_modelos.name_space", "LIKE", "%$search%")
            ->orWhere("sys_modelos.modelo_relacionado", "LIKE", "%$search%")
            ->orWhere("sys_modelos.ruta_storage_imagen", "LIKE", "%$search%")
            ->orWhere("sys_modelos.enlaces", "LIKE", "%$search%")
            ->orWhere("sys_modelos.modelo", "LIKE", "%$search%")
            ->orWhere("sys_modelos.url_ver", "LIKE", "%$search%")
            ->orWhere("sys_modelos.url_print", "LIKE", "%$search%")
            ->orWhere("sys_modelos.url_eliminar", "LIKE", "%$search%")
            ->orderBy('sys_modelos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MODELOS (CRUD)";
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Campo
    */
    public static function get_tabla($registro_modelo_padre, $registros_asignados)
    {
        $encabezado_tabla = ['Orden', 'ID', 'Tipo', 'Name', 'Descripción', 'Opciones', 'Valor', 'Atributos', 'Requerido', 'Editable', 'Único', 'Acción']; // 12 campos

        $registros = [];
        $i = 0;
        foreach ($registros_asignados as $fila) {
            $orden = DB::table('sys_modelo_tiene_campos')->where('core_campo_id', '=', $fila['id'])
                ->where('core_modelo_id', '=', $registro_modelo_padre->id)
                ->value('orden');

            $registros[$i] = collect([
                $orden,
                $fila['id'],
                $fila['tipo'],
                $fila['name'],
                $fila['descripcion'],
                $fila['opciones'],
                $fila['value'],
                $fila['atributos'],
                $fila['requerido'],
                $fila['editable'],
                $fila['unico']
            ]); // 11 campos
            $i++;
        }

        return View::make('core.modelos.tabla_modelo_relacionado', compact('encabezado_tabla', 'registros', 'registro_modelo_padre'))->render();
    }

    public static function get_opciones_modelo_relacionado($core_modelo_id)
    {
        $vec[''] = '';
        $opciones = DB::table('sys_campos')->get();
        foreach ($opciones as $opcion) {
            $esta = DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', $core_modelo_id)->where('core_campo_id', $opcion->id)->get();
            if (empty($esta)) {
                $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion . ' (' . $opcion->tipo . ')' . ' (' . $opcion->name . ')' . ' (' . $opcion->requerido . ' - ' . $opcion->editable . ')' . ' (' . $opcion->opciones . ')';
            }
        }
        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'sys_modelo_tiene_campos';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'core_modelo_id';
        $registro_modelo_hijo_id = 'core_campo_id';

        return compact('nombre_tabla', 'nombre_columna1', 'registro_modelo_padre_id', 'registro_modelo_hijo_id');
    }

    /** FIN ***/

    public static function opciones_campo_select()
    {
        $opciones = Modelo::select('id', 'descripcion')
            ->orderBy('descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->id . ')';
        }

        return $vec;
    }
}
