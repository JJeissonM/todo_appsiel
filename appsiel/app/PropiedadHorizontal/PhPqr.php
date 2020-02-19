<?php

namespace App\PropiedadHorizontal;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;


class PhPqr extends Model
{
    protected $fillable = ['core_empresa_id','asunto','detalle','grado_satisfaccion','fecha','prioridad','user_asignado_id','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['Creado por','Asunto','Detalle','Fecha','Prioridad','Estado','Acción'];

    public static function consultar_registros()
    {
        $empresa_id = Auth::user()->empresa_id;

        $usuario = Auth::user();

        if($usuario->hasRole('Residente PH')){

            $registros = PhPqr::where('ph_pqrs.core_empresa_id',$empresa_id)
            ->where('ph_pqrs.creado_por',Auth::user()->email )
                    ->select('ph_pqrs.creado_por AS campo1','ph_pqrs.asunto AS campo2','ph_pqrs.detalle AS campo3','ph_pqrs.fecha AS campo4','ph_pqrs.prioridad AS campo5','ph_pqrs.estado AS campo6','ph_pqrs.id AS campo7')
                    ->get()
                    ->toArray();
        }else{
            $registros = PhPqr::where('ph_pqrs.core_empresa_id',$empresa_id)
                    ->select('ph_pqrs.creado_por AS campo1','ph_pqrs.asunto AS campo2','ph_pqrs.detalle AS campo3','ph_pqrs.fecha AS campo4','ph_pqrs.prioridad AS campo5','ph_pqrs.estado AS campo6','ph_pqrs.id AS campo7')
                    ->get()
                    ->toArray();
        }        

        return $registros;
    }

    public function notas()
    {
        return $this->hasMany('App\PropiedadHorizontal\PhNotasPqr');
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
                        $encabezado_tabla = ['Fecha/Hora','Detalle','Creado por','Acción'];
                        for($i=0;$i<count($encabezado_tabla);$i++){
                            $tabla.='<th>'.$encabezado_tabla[$i].'</th>';
                        }
                $tabla.='</thead>
                    <tbody>';
                        foreach($registros_asignados as $fila){
                            $tabla.='<tr>';
                                $tabla.='<td>'.$fila['fecha'].'</td>';
                                $tabla.='<td>'.$fila['detalle'].'</td>';
                                $tabla.='<td>'.$fila['creado_por'].'</td>';
                                $tabla.='<td>
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
        $vec['']='PQR';
        return $vec;
    }
}
