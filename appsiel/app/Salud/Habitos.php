<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Modelo;
use App\Core\ModeloEavValor;

use App\Salud\ConsultaMedica;

use DB;

class Habitos extends Model
{
    protected $table = 'core_eav_valores';

	protected $fillable = ['modelo_padre_id', 'registro_modelo_padre_id', 'modelo_entidad_id', 'core_campo_id', 'valor'];
	
	protected $crud_model_id = 96; // Es el mismo $modelo_padre_id, la variable no se puede usar en métodos estáticos

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"consultorio_medico/pacientes/id_fila"}';

	public $encabezado_tabla = [ 'ID', 'Campo', 'Valor', 'Acción'];

	public static function consultar_registros()
	{
		$modelo_padre_id = 239;
	    return Habitos::leftJoin('sys_campos', 'sys_campos.id', '=', 'core_eav_valores.core_campo_id')
	    			->where('core_eav_valores.modelo_padre_id',$modelo_padre_id)
                    ->select(
                    			'core_eav_valores.id AS campo1',
                                'sys_campos.descripcion AS campo2',
                                'core_eav_valores.valor AS campo3',
                    			'core_eav_valores.id AS campo4')
				    ->get()
				    ->toArray();
	}


    public static function opciones_campo_select()
    {
    	$modelo_padre_id = 96; // Consultas
        $opciones = Habitos::where('core_eav_valores.modelo_padre_id',$modelo_padre_id)
                                    ->orderBy('valor')
                                    ->get();
                            
        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->modelo_padre_id.'-'.$opcion->registro_modelo_padre_id.'-'.$opcion->modelo_entidad_id.'-'.$opcion->core_campo_id] = $opcion->valor;
        }
        return $vec;
    }

    public function get_campos_adicionales_create( $lista_campos )
    {
        $modelo_padre_id = Modelo::find( $this->crud_model_id )->id;

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            if ( $lista_campos[$i]['name'] == 'modelo_padre_id' ) 
            {
                $lista_campos[$i]['value'] = $modelo_padre_id;
            }
        }

        return $lista_campos;
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

		// Se va a crear un registro por cada Atributo (campo) que tenga un Valor distinto a vacío 
        foreach ( $datos as $key => $value) 
        {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $key, "core_campo_id") !== false ) 
            {
                $core_campo_id = explode("-", $key)[1]; // Atributo
                $valor = $value; // Valor
                
                if ( is_array($value) )
                {
                    $valor = implode(",", $value);
                }

				ModeloEavValor::create( [ 
                                            "modelo_padre_id" => $datos['modelo_padre_id'],
                                            "registro_modelo_padre_id" => $datos['registro_modelo_padre_id'],
                                            "modelo_entidad_id" => $datos['modelo_entidad_id'],
                                            "core_campo_id" => $core_campo_id,
                                            "valor" => $valor 
                                        ] );
            }
        }
        $id_modelo = 95; // Pacientes
        $consulta = ConsultaMedica::find( $datos['registro_modelo_padre_id'] );
        return 'consultorio_medico/pacientes/' . $consulta->paciente->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $id_modelo;
    }

    public function get_campos_adicionales_edit( $lista_campos, $registro )
    {
        $modelo_padre_id = Modelo::find( $this->crud_model_id )->id;

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $lista_campos[$i]['name'], "core_campo_id") !== false ) 
            {
                $core_campo_id = $lista_campos[$i]['id']; // Atributo_ID

                $registro_eav = ModeloEavValor::where( [ 
                                                        "modelo_padre_id" => $modelo_padre_id,
                                                        "registro_modelo_padre_id" => $registro->registro_modelo_padre_id,
                                                        "modelo_entidad_id" => $registro->modelo_entidad_id,
                                                        "core_campo_id" => $core_campo_id
                                                    ] )
                                                ->get()
                                                ->first();

                if( !is_null( $registro_eav ) )
                {
                    $lista_campos[$i]['value'] = $registro_eav->valor;
                }
            }

        }

        return $lista_campos;
    }

    public function update_adicional( $datos, $id )
    {
        // Se va a actualizar/crear un registro por cada Atributo (campo)
        foreach ( $datos as $key => $value) 
        {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $key, "core_campo_id") !== false ) 
            {
                $core_campo_id = explode("-", $key)[1]; // Atributo
                $valor = $value; // Valor

                if ( is_array($value) )
                {
                    $valor = implode(",", $value);
                }

                $registro_eav = ModeloEavValor::where( [ 
                                                            "modelo_padre_id" => $datos['modelo_padre_id'],
                                                            "registro_modelo_padre_id" => $datos['registro_modelo_padre_id'],
                                                            "modelo_entidad_id" => $datos['modelo_entidad_id'],
                                                            "core_campo_id" => $core_campo_id
                                                        ] )
                                                ->get()
                                                ->first();

                if ( is_null( $registro_eav ) )
                {
                    ModeloEavValor::create( [ 
                                            "modelo_padre_id" => $datos['modelo_padre_id'],
                                            "registro_modelo_padre_id" => $datos['registro_modelo_padre_id'],
                                            "modelo_entidad_id" => $datos['modelo_entidad_id'],
                                            "core_campo_id" => $core_campo_id,
                                            "valor" => $valor 
                                        ] );
                }else{
                    $registro_eav->valor = $valor;
                    $registro_eav->save();
                }
            }
        }

        $id_modelo = 95; // Pacientes
        $consulta = ConsultaMedica::find( $datos['registro_modelo_padre_id'] );
        return 'consultorio_medico/pacientes/' . $consulta->paciente->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $id_modelo;
    }


    public static function get_valor_campo( $string_ids_campos )
    {
        $vec_ids_campos = explode('-', $string_ids_campos);

        if ( !isset( $vec_ids_campos[1] ) )
        {
        	return '';
        }

        return ModeloEavValor::where( [ 
                                        "modelo_padre_id" => $vec_ids_campos[0],
                                        "registro_modelo_padre_id" => $vec_ids_campos[1],
                                        "core_campo_id" => $vec_ids_campos[3]
                                    ] )
                            ->value('valor');
    }
}
