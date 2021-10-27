<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Modelo;
use App\Sistema\Services\FieldsList;

use DB;

class Endodoncia extends Modelo
{
    protected $table = 'salud_endodoncia';

	protected $fillable = ['paciente_id', 'consulta_id', 'numero_diente', 'frio', 'caliente', 'percusion_horizontal', 'percusion_vertical', 'observaciones'];
	
	protected $crud_model_id = 308;

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"consultorio_medico/pacientes/id_fila","update":"core/eav/id_fila"}';

    public $vista_imprimir = 'consultorio_medico.salud_ocupacional.examen_fisico';

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Campo', 'Valor'];

    public function get_fields_to_show()
    {
        $fields_list = new FieldsList( $this->crud_model_id, $this );
        return $fields_list->get_list_to_show();
    }
    
    public function store_adicional( $datos, $registro )
    {
    	// 
    }
}
