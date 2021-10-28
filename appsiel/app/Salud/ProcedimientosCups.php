<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Modelo;
use App\Sistema\Services\FieldsList;

use App\Salud\ConsultaMedica;

use DB;

class ProcedimientosCups extends Model
{
    protected $table = 'salud_procedimientos_cups_consultas';

	protected $fillable = ['paciente_id', 'consulta_id', 'diagnostico_cie_principal_id', 'diagnostico_cie_relacionado_id', 'diagnostico_cie_de_complicacion_id', 'fecha_procedimiento', 'numero_autorizacion', 'codigo_cups', 'ambito_realizacion_procedimiento', 'finalidad_procedimiento', 'personal_al_que_atiende', 'forma_realizacion_acto_quirurgico', 'observaciones', 'valor_procedimiento'];	

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"consultorio_medico/pacientes/id_fila","update":"core/eav/id_fila"}';

    public $vista_imprimir = 'consultorio_medico.salud_ocupacional.examen_fisico';

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Campo', 'Valor'];
    
    protected $crud_model_id = 310;

    public function get_fields_to_show()
    {
        $fields_list = new FieldsList( $this->crud_model_id, $this );
        return $fields_list->get_list_to_show();
    }

    public static function consultar_registros($nro_registros)
    {
        return ProcedimientosCups::leftJoin('sys_campos', 'sys_campos.id', '=', 'core_eav_valores.core_campo_id')
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
