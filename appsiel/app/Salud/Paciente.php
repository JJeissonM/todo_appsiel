<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use DB;

class Paciente extends Model
{
    protected $table = 'salud_pacientes';

    protected $fillable = ['core_tercero_id', 'codigo_historia_clinica', 'fecha_nacimiento', 'genero', 'ocupacion', 'estado_civil', 'grupo_sanguineo', 'remitido_por', 'nivel_academico'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Codigo historia clínica', 'Nombre completo', 'Doc. Identidad', 'Fecha nacimiento', 'Género', 'Grupo Sanguineo'];

    //public $vistas = '{"index":"consultorio_medico.pacientes_index"}';

    public $urls_acciones = '{
                                "create":"web/create",
                                "edit":"web/id_fila/edit",
                                "show":"consultorio_medico/pacientes/id_fila",
                                "store":"consultorio_medico/pacientes",
                                "update":"consultorio_medico/pacientes/id_fila"}';

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public function consultas()
    {
        return $this->hasMany('App\Salud\ConsultaMedica', 'paciente_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = "TRIM(CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres,' ',core_terceros.apellido1,' ',core_terceros.apellido2)) AS campo2";

        return Paciente::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
            ->select(
                'salud_pacientes.codigo_historia_clinica as campo1',
                DB::raw($select_raw),
                'core_terceros.numero_identificacion as campo3',
                'salud_pacientes.fecha_nacimiento as campo4',
                'salud_pacientes.genero as campo5',
                'salud_pacientes.grupo_sanguineo as campo6',
                'salud_pacientes.id as campo7'
            )
            ->where("salud_pacientes.codigo_historia_clinica", "LIKE", "%$search%")
            ->orWhere(DB::raw("TRIM(CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres,' ',core_terceros.apellido1,' ',core_terceros.apellido2))"), "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("salud_pacientes.fecha_nacimiento", "LIKE", "%$search%")
            ->orWhere("salud_pacientes.genero", "LIKE", "%$search%")
            ->orWhere("salud_pacientes.grupo_sanguineo", "LIKE", "%$search%")
            ->orderBy('salud_pacientes.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $select_raw = "TRIM(CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres,' ',core_terceros.apellido1,' ',core_terceros.apellido2)) AS NOMBRE_COMPLETO";

        $string = Paciente::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
            ->select(
                'salud_pacientes.codigo_historia_clinica as CODIGO_HISTORIA_CLÍNICA',
                DB::raw($select_raw),
                'core_terceros.numero_identificacion as DOC_IDENTIDAD',
                'salud_pacientes.fecha_nacimiento as FECHA_NACIMIENTO',
                'salud_pacientes.genero as GÉNERO',
                'salud_pacientes.grupo_sanguineo as GRUPO_SANGUINEO'
            )
            ->where("salud_pacientes.codigo_historia_clinica", "LIKE", "%$search%")
            ->orWhere(DB::raw("TRIM(CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres,' ',core_terceros.apellido1,' ',core_terceros.apellido2))"), "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("salud_pacientes.fecha_nacimiento", "LIKE", "%$search%")
            ->orWhere("salud_pacientes.genero", "LIKE", "%$search%")
            ->orWhere("salud_pacientes.grupo_sanguineo", "LIKE", "%$search%")
            ->orderBy('salud_pacientes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PACIENTES";
    }

    public static function get_campos_adicionales_edit($lista_campos, &$registro)
    {
        $registro->nombre1 = $registro->tercero->nombre1;
        $registro->otros_nombres = $registro->tercero->otros_nombres;
        $registro->apellido1 = $registro->tercero->apellido1;
        $registro->apellido2 = $registro->tercero->apellido2;
        $registro->id_tipo_documento_id = $registro->tercero->id_tipo_documento_id;
        $registro->numero_identificacion = $registro->tercero->numero_identificacion;
        $registro->direccion1 = $registro->tercero->direccion1;
        $registro->telefono1 = $registro->tercero->telefono1;
        $registro->email = $registro->tercero->email;

        return $lista_campos;
    }


    public static function update_adicional($datos, $cliente_id)
    {
        $registro = Paciente::find($id);

        // Actualizar datos del Tercero
        $registro->tercero->fill($datos);
        $registro->tercero->save();

        return true;
    }

    public static function consultar_registros2()
    {
        /* Esto no se va a usar, solo es para que enviar algo en este método */
        return Paciente::find(1)->paginate(10);
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/salud_pacientes.js';

    public static function datos_basicos_historia_clinica($paciente_id)
    {
        return Paciente::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
            ->where('salud_pacientes.id', $paciente_id)
            ->select(
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombres'),
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2) AS apellidos'),
                'salud_pacientes.codigo_historia_clinica AS codigo',
                'salud_pacientes.fecha_nacimiento',
                'salud_pacientes.genero',
                'salud_pacientes.estado_civil',
                'salud_pacientes.grupo_sanguineo',
                'salud_pacientes.remitido_por',
                'salud_pacientes.nivel_academico',
                'salud_pacientes.ocupacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1',
                'core_terceros.numero_identificacion',
                'core_terceros.email',
                'core_terceros.imagen'
            )
            ->get()
            ->first();
    }


    public static function opciones_campo_select()
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        $opciones = Paciente::leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
            ->select('salud_pacientes.id', 'core_terceros.descripcion')
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
