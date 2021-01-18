<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use DB;

class Anamnesis extends Model
{
    protected $table = 'core_eav_valores';
	protected $fillable = ['modelo_principal_id', 'registro_modelo_principal_id', 'modelo_relacionado_id', 'core_campo_id', 'valor'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre completo', 'Doc. Identidad', 'Codigo historia clínica', 'Fecha nacimiento', 'Género', 'Grupo Sanguineo'];

	public static function consultar_registros($nro_registros)
	{
		$select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';

		$registros = Anamnesis::leftJoin('core_terceros', 'core_terceros.id', '=', 'core_eav_valores.core_tercero_id')
			->select(DB::raw($select_raw), 'core_terceros.numero_identificacion AS campo2', 'core_eav_valores.codigo_historia_clinica AS campo3', 'core_eav_valores.fecha_nacimiento AS campo4', 'core_eav_valores.genero AS campo5', 'core_eav_valores.grupo_sanguineo AS campo6', 'core_eav_valores.id AS campo7')
			->orderBy('core_eav_valores.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function consultas()
    {
        return $this->hasMany('App\Salud\ConsultaMedica','anamnesis_id');
    }
}
