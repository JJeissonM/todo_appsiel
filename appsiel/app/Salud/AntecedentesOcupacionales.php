<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Modelo;
use App\Core\ModeloEavValor;

use App\Salud\ConsultaMedica;

use DB;

class AntecedentesOcupacionales extends ModeloEavValor
{
    protected $table = 'core_eav_valores';

	protected $fillable = ['modelo_padre_id', 'registro_modelo_padre_id', 'modelo_entidad_id', 'core_campo_id', 'valor'];
	
	protected $crud_model_id = 96; // Consultas

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"consultorio_medico/pacientes/id_fila","update":"core/eav/id_fila"}';

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Campo', 'Valor'];

    public static function consultar_registros($nro_registros)
    {
        $modelo_padre_id = 237; // Antecedentes ocupacionales
        return AntecedentesOcupacionales::leftJoin('sys_campos', 'sys_campos.id', '=', 'core_eav_valores.core_campo_id')
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
    	// Con ModeloController se almacena un solo registro en la tabla EAV
    	// Se elimina ese registro para crear los nuevos desde aquí
    	ModeloEavValor::where(
                                [ 
                                    "modelo_padre_id" => $datos['modelo_padre_id'],
                                    "registro_modelo_padre_id" => $datos['registro_modelo_padre_id'],
                                    "modelo_entidad_id" => $datos['modelo_entidad_id'],
                                    "core_campo_id" => 0,
                                    "valor" => ''
                                ]
                            )
                        ->delete();



        $datos2 = array_shift($datos); // Eliminar primer campo del request: _token

		$this->almacenar_registros_eav( $datos );

        $id_modelo = 95; // Pacientes
        $consulta = ConsultaMedica::find( $datos['registro_modelo_padre_id'] );
        return 'consultorio_medico/pacientes/' . $consulta->paciente->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $id_modelo;
    }
}
