<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use DB;

class ProfesionalSalud extends Model
{
    protected $table = 'salud_profesionales';
    protected $fillable = ['core_tercero_id', 'especialidad', 'numero_carnet_licencia', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre completo', 'Especialidad', 'Registro médico', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        $registros = ProfesionalSalud::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_profesionales.core_tercero_id')
            ->select(
                DB::raw($select_raw),
                'salud_profesionales.especialidad AS campo2',
                'salud_profesionales.numero_carnet_licencia AS campo3',
                'salud_profesionales.estado AS campo4',
                'salud_profesionales.id AS campo5'
            )
            ->where(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2)'), "LIKE", "%$search%")
            ->orWhere("salud_profesionales.especialidad", "LIKE", "%$search%")
            ->orWhere("salud_profesionales.numero_carnet_licencia", "LIKE", "%$search%")
            ->orWhere("salud_profesionales.estado", "LIKE", "%$search%")
            ->orderBy('salud_profesionales.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS NOMBRE_COMPLETO';

        $string = ProfesionalSalud::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_profesionales.core_tercero_id')
            ->select(
                DB::raw($select_raw),
                'salud_profesionales.especialidad AS ESPECIALIDAD',
                'salud_profesionales.numero_carnet_licencia AS REGISTRO_MÉDICO',
                'salud_profesionales.estado AS ESTADO'
            )
            ->where(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2)'), "LIKE", "%$search%")
            ->orWhere("salud_profesionales.especialidad", "LIKE", "%$search%")
            ->orWhere("salud_profesionales.numero_carnet_licencia", "LIKE", "%$search%")
            ->orWhere("salud_profesionales.estado", "LIKE", "%$search%")
            ->orderBy('salud_profesionales.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PROFESIONALES DE LA SALUD";
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/consultorio_medico/profesional_salud.js';

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public static function opciones_campo_select()
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        $opciones = ProfesionalSalud::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_profesionales.core_tercero_id')
            ->select('salud_profesionales.id', 'core_terceros.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function citamedicas()
    {
        return $this->hasMany(Citamedica::class);
    }
}
