<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use DB;

class Paciente extends Model
{
    protected $table = 'salud_pacientes';
	
    protected $fillable = ['core_tercero_id', 'codigo_historia_clinica', 'fecha_nacimiento', 'genero', 'ocupacion', 'estado_civil', 'grupo_sanguineo', 'remitido_por', 'nivel_academico'];

	public $encabezado_tabla = ['Nombre completo', 'Doc. Identidad', 'Codigo historia clínica', 'Fecha nacimiento', 'Género', 'Grupo Sanguineo', 'Acción'];
    public static function consultar_registros()
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';

        $registros = Paciente::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
                    ->select(DB::raw($select_raw), 'core_terceros.numero_identificacion AS campo2', 'salud_pacientes.codigo_historia_clinica AS campo3', 'salud_pacientes.fecha_nacimiento AS campo4', 'salud_pacientes.genero AS campo5', 'salud_pacientes.grupo_sanguineo AS campo6', 'salud_pacientes.id AS campo7')
        ->get()
        ->take(20)
        ->toArray();
        return $registros;
    }

    public static function consultar_datatable()
    {
        $select_raw = "TRIM(CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres,' ',core_terceros.apellido1,' ',core_terceros.apellido2)) AS nombre_completo";

        $registros = Paciente::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
                    ->select(DB::raw($select_raw), 
                            'core_terceros.numero_identificacion', 
                            'salud_pacientes.codigo_historia_clinica', 
                            'salud_pacientes.fecha_nacimiento', 
                            'salud_pacientes.genero', 
                            'salud_pacientes.grupo_sanguineo', 
                            'salud_pacientes.id');
        return $registros;
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/salud_pacientes.js';

	public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function consultas()
    {
        return $this->hasMany('App\Salud\ConsultaMedica','paciente_id');
    }

    public static function datos_basicos_historia_clinica( $paciente_id )
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombres';

        $select_raw2 = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2) AS apellidos';

        return Paciente::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
                    ->where('salud_pacientes.id',$paciente_id)->select(DB::raw($select_raw), DB::raw($select_raw2), 'salud_pacientes.codigo_historia_clinica AS codigo', 'salud_pacientes.fecha_nacimiento', 'salud_pacientes.genero', 'salud_pacientes.estado_civil', 'salud_pacientes.grupo_sanguineo', 'salud_pacientes.nivel_academico', 'salud_pacientes.ocupacion', 'core_terceros.direccion1', 'core_terceros.telefono1', 'core_terceros.numero_identificacion', 'core_terceros.email', 'core_terceros.imagen')
                    ->get()[0];
    }
    

    public static function opciones_campo_select()
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        $opciones = Paciente::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
                    ->select('salud_pacientes.id','core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
