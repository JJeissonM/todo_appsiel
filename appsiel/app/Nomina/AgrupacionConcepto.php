<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use View;
use DB;

class AgrupacionConcepto extends Model
{
    protected $table = 'nom_agrupaciones_conceptos';
    protected $fillable = ['core_empresa_id', 'descripcion', 'nombre_corto', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Nombre corto', 'Estado'];
    public static function consultar_registros($nro_registros)
    {
        $registros = AgrupacionConcepto::select('nom_agrupaciones_conceptos.descripcion AS campo1', 'nom_agrupaciones_conceptos.nombre_corto AS campo2', 'nom_agrupaciones_conceptos.estado AS campo3', 'nom_agrupaciones_conceptos.id AS campo4')
            ->orderBy('nom_agrupaciones_conceptos.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = AgrupacionConcepto::where('nom_agrupaciones_conceptos.estado', 'Activo')
            ->select('nom_agrupaciones_conceptos.id', 'nom_agrupaciones_conceptos.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function conceptos()
    {
        return $this->belongsToMany('App\Nomina\NomConcepto', 'nom_agrupacion_tiene_conceptos', 'nom_agrupacion_id', 'nom_concepto_id');
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso NomConcepto
    */
    public static function get_tabla($registro_modelo_padre, $registros_asignados)
    {
        $encabezado_tabla = ['Orden', 'ID', 'Modo Liquidación', 'Descripción', 'Abreviatura', 'Porc. sobre el básico', 'Naturaleza', 'Estado', 'Acción']; // 9 campos

        $registros = [];
        $i = 0;
        foreach ($registros_asignados as $fila) {
            $orden = DB::table('nom_agrupacion_tiene_conceptos')->where('nom_concepto_id', '=', $fila['id'])
                ->where('nom_agrupacion_id', '=', $registro_modelo_padre->id)
                ->value('orden');

            $registros[$i] = collect([
                $orden,
                $fila['id'],
                $fila['modo_liquidacion_id'],
                $fila['descripcion'],
                $fila['abreviatura'],
                $fila['porcentaje_sobre_basico'],
                $fila['naturaleza'],
                $fila['estado']
            ]);
            $i++;
        }

        return View::make('core.modelos.tabla_modelo_relacionado', compact('encabezado_tabla', 'registros', 'registro_modelo_padre'))->render();
    }

    public static function get_opciones_modelo_relacionado($nom_agrupacion_id)
    {
        $vec[''] = '';
        $opciones = DB::table('nom_conceptos')->get();
        foreach ($opciones as $opcion) {
            $esta = DB::table('nom_agrupacion_tiene_conceptos')->where('nom_agrupacion_id', $nom_agrupacion_id)->where('nom_concepto_id', $opcion->id)->get();
            if (empty($esta)) {
                $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion;
            }
        }
        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'nom_agrupacion_tiene_conceptos';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'nom_agrupacion_id';
        $registro_modelo_hijo_id = 'nom_concepto_id';

        return compact('nombre_tabla', 'nombre_columna1', 'registro_modelo_padre_id', 'registro_modelo_hijo_id');
    }

    /** FIN ***/
}
