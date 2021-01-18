<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    protected $table = 'salud_consultorios';
    protected $fillable = ['descripcion', 'sede', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Sede', 'Estado'];
    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Consultorio::select(
            'salud_consultorios.descripcion AS campo1',
            'salud_consultorios.sede AS campo2',
            'salud_consultorios.estado AS campo3',
            'salud_consultorios.id AS campo4'
        )
            ->where("salud_consultorios.descripcion", "LIKE", "%$search%")
            ->orWhere("salud_consultorios.sede", "LIKE", "%$search%")
            ->orWhere("salud_consultorios.estado", "LIKE", "%$search%")
            ->orderBy('salud_consultorios.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Consultorio::select(
            'salud_consultorios.descripcion AS DESCRIPCIÓN',
            'salud_consultorios.sede AS SEDE',
            'salud_consultorios.estado AS ESTADO'
        )
            ->where("salud_consultorios.descripcion", "LIKE", "%$search%")
            ->orWhere("salud_consultorios.sede", "LIKE", "%$search%")
            ->orWhere("salud_consultorios.estado", "LIKE", "%$search%")
            ->orderBy('salud_consultorios.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONSULTORIOS";
    }

    public static function opciones_campo_select()
    {
        $opciones = Consultorio::select('id', 'descripcion')
            ->get();

        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class);
    }

    public function citamedicas()
    {
        return $this->hasMany(Citamedica::class);
    }
}
