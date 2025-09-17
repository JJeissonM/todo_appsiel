<?php 

namespace App\Sistema\Services;

use App\Sistema\Modelo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrudService
{
    public function validar_eliminacion_un_registro( int $registro_id, string $tablas_relacionadas)
    {
        $tablas = json_decode( $tablas_relacionadas );

        foreach($tablas AS $una_tabla)
        { 
            if ( !Schema::hasTable( $una_tabla->tabla ) )
            {
                continue;
            }
            
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $registro_id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }

    public function get_model_dataform( $model_id, $record, $action, $variables_url, $form_id)
    {
        $modelo = Modelo::find( $model_id );

        $actions = (new ModeloService())->acciones_basicas_modelo($modelo, $variables_url);

        $title = 'Ventana';

        switch ($action) {
            case 'create':
                $title = 'Crear Nuevo Registro';
                break;
            
            default:
                # code...
                break;
        }

        $data = [
            'windows_info' => [
                            'title' => $title,
                            'action' => $action
                        ],
            'form_info' => [
                            'action' => url('/') . '/' . $actions->store,
                            'id' => $form_id
                        ],
            'fields' => $this->format_select_input( (new ModeloService())->get_campos_modelo($modelo, $record, $action) )
        ];
        
        return json_encode( $data );
    }

    public function format_select_input( array $lista_campos )
    {
        $cant = count($lista_campos);
        for ($i = 0; $i < $cant; $i++)
        {
            // Para llenar los campos tipo select, checkbox y multiselect_autocomplete
            if ($lista_campos[$i]['tipo'] == 'select' || $lista_campos[$i]['tipo'] == 'bsCheckBox' || $lista_campos[$i]['tipo'] == 'multiselect_autocomplete')
            {
                $opciones = $lista_campos[$i]['opciones'];
                $new_options = collect();
                foreach ($opciones as $key => $value)
                {
                    $new_options->push([
                        'id' => $key,
                        'label' => $value
                    ]);
                }

                $lista_campos[$i]['opciones'] = $new_options->toArray();
            }
        }
        return $lista_campos;
    }

}