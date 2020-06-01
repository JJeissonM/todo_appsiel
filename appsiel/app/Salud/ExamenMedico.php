<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;

use App\Sistema\Modelo;

class ExamenMedico extends Model
{
    protected $table = 'salud_examenes';
	protected $fillable = ['descripcion', 'detalle', 'orden', 'estado'];
	public $encabezado_tabla = ['Código', 'Descripción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ExamenMedico::select('salud_examenes.descripcion AS campo1', 'salud_examenes.detalle AS campo2', 'salud_examenes.estado AS campo3', 'salud_examenes.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = ExamenMedico::select('id','descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function examenes_del_paciente( $paciente_id, $consulta_id )
    {
        $modelo_resultados_examenes = Modelo::where('modelo','salud_resultados_examenes')->first();

        // Advertencia: ¿Cómo se revisan los resultados de los exámenes INACTIVOS?
        
        $opciones = ExamenMedico::where('estado','Activo')->orderBy('orden')->get();
        $vec = [];
        foreach ($opciones as $opcion)
        {
            $esta = DB::table('salud_resultados_examenes')->where('examen_id',$opcion->id)->where('paciente_id',$paciente_id)->where('consulta_id',$consulta_id)->first();
            
            if ( is_null($esta) ) 
            {
                $vec[] = '<a class="btn btn-primary btn-xs" href="'.url( 'consultorio_medico/resultado_examen_medico/create?id='.Input::get('id').'&id_modelo='.$modelo_resultados_examenes->id.'&paciente_id='.$paciente_id.'&consulta_id='.$consulta_id.'&examen_id='.$opcion->id ).'"> <i class="fa fa-plus"></i> '.$opcion->descripcion.' </a> &nbsp;&nbsp;&nbsp;';
            }else{
                $vec[] = '<button class="btn btn-default btn-xs btn_ver_examen" data-paciente_id="'.$paciente_id.'" data-consulta_id="'.$consulta_id.'" data-examen_id="'.$opcion->id.'" data-examen_descripcion="'.$opcion->descripcion.'"> <i class="fa fa-eye"></i> '.$opcion->descripcion.' </button> &nbsp;&nbsp;&nbsp;';
            }
        }

        return $vec;
    }

	/*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Campo
    */
    public static function get_tabla($registro_modelo_padre,$registros_asignados)
    {
        //$nombre = User::where('email',$registro_modelo_padre->creado_por)->name();
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="myTable">
                    <thead>';
                        $encabezado_tabla = ['Descripción', 'Abreviatura', 'Orden', 'Estado', 'Acción'];
                        for($i=0;$i<count($encabezado_tabla);$i++){
                            $tabla.='<th>'.$encabezado_tabla[$i].'</th>';
                        }
                $tabla.='</thead>
                    <tbody>';
                        foreach($registros_asignados as $fila){
                            $tabla.='<tr>';
                                $tabla.='<td>'.$fila['descripcion'].'</td>';
                                $tabla.='<td>'.$fila['abreviatura'].'</td>';
                                $tabla.='<td>'.$fila['orden'].'</td>';
                                $tabla.='<td>'.$fila['estado'].'</td>';
                                $tabla.='<td>
                                        <a class="btn btn-danger btn-sm" href="'.url('web/eliminar_asignacion/registro_modelo_hijo_id/'.$fila['id'].'/registro_modelo_padre_id/'.$registro_modelo_padre->id.'/id_app/'.Input::get('id').'/id_modelo_padre/'.Input::get('id_modelo')).'"><i class="fa fa-btn fa-trash"></i> </a>
                                        </td>
                            </tr>';
                        }
                    $tabla.='</tbody>
                </table>
            </div>';
        return $tabla;
    }

    public static function get_opciones_modelo_relacionado($ph_pqr_id)
    {
        $vec['']='AsignarVariableExamen';
        return $vec;
    }
}
