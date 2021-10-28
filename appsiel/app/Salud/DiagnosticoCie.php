<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Modelo;
use App\Sistema\Services\FieldsList;

use DB;

use App\Salud\CodigoCie;

class DiagnosticoCie extends Model
{
    protected $table = 'salud_diagnosticos_cie_consultas';

    // codigo_cie: almacena el ID del Codigo Cie
	protected $fillable = ['paciente_id', 'consulta_id', 'es_diagnostico_principal', 'codigo_cie', 'tipo_diagnostico_principal', 'observaciones'];

    protected $crud_model_id = 309;

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"consultorio_medico/pacientes/id_fila","update":"core/eav/id_fila"}';

    public $vista_imprimir = 'consultorio_medico.salud_ocupacional.examen_fisico';

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Campo', 'Valor'];

    public function codigo_diagnostico()
    {
        return $this->belongsTo( CodigoCie::class, 'codigo_cie');
    }

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
