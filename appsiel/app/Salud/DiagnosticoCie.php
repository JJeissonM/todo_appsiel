<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Modelo;
use App\Core\ModeloEavValor;

use App\Salud\ConsultaMedica;

use DB;

class DiagnosticoCie extends ModeloEavValor
{
    protected $table = 'salud_diagnosticos_cie_consultas';

	protected $fillable = ['paciente_id', 'consulta_id', 'es_diagnostico_principal', 'codigo_cie', 'tipo_diagnostico_principal', 'observaciones'];
	

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"consultorio_medico/pacientes/id_fila","update":"core/eav/id_fila"}';

    public $vista_imprimir = 'consultorio_medico.salud_ocupacional.examen_fisico';

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Campo', 'Valor'];

    public static function consultar_registros($nro_registros)
    {
        return DiagnosticoCie::leftJoin('sys_campos', 'sys_campos.id', '=', 'core_eav_valores.core_campo_id')
            ->where('core_eav_valores.modelo_padre_id', $modelo_padre_id)
            ->select(
                'sys_campos.descripcion AS campo1',
                'core_eav_valores.valor AS campo2',
                'core_eav_valores.id AS campo3'
            )
            ->orderBy('core_eav_valores.created_at', 'DESC')
            ->paginate($nro_registros);
    }
    
    public function store_adicional( $datos, $registro )
    {
    	//
    }
}
