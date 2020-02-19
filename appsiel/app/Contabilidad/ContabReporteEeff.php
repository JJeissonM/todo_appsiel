<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;

use App\Contabilidad\ContabCuentaGrupo;

class ContabReporteEeff extends Model
{
	protected $table = 'contab_reportes_eeff';

    protected $fillable = ['core_empresa_id','descripcion', 'factor_expresion_valores'];

    public $encabezado_tabla = ['ID','Descripción','Expresión de valores','Acción'];

    public static function consultar_registros()
    {
        $registros = ContabReporteEeff::where('contab_reportes_eeff.core_empresa_id',Auth::user()->empresa_id)
                    ->select('contab_reportes_eeff.id AS campo1','contab_reportes_eeff.descripcion AS campo2','contab_reportes_eeff.factor_expresion_valores AS campo3','contab_reportes_eeff.id AS campo4')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function grupos_cuentas()
    {
        return $this->belongsToMany('App\Contabilidad\ContabCuentaGrupo','contab_reporte_tiene_grupos_cuentas','contab_reporte_id','contab_grupo_cuenta_id');
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Bloques_eeff
    */

        // Tabla para visualizar registros asignados
        // En la vista show del modelo padre
    public static function get_tabla($registro_modelo_padre,$registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="myTable">
                    <thead>';
                        $encabezado_tabla = ['Orden','ID','Descripción','Acción'];
                        for($i=0;$i<count($encabezado_tabla);$i++){
                            $tabla.='<th>'.$encabezado_tabla[$i].'</th>';
                        }
                $tabla.='</thead>
                    <tbody>';
                        foreach($registros_asignados as $fila){
                            $orden = DB::table('contab_reporte_tiene_grupos_cuentas')->where('contab_grupo_cuenta_id', '=', $fila['id'])
                                        ->where('contab_reporte_id', '=', $registro_modelo_padre->id)
                                        ->value('orden');

                            $tabla.='<tr>';
                            $tabla.='<td>'.$orden.'</td>';
                            $tabla.='<td>'.$fila['id'].'</td>';
                            $tabla.='<td>'.$fila['descripcion'].'</td>';
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

    // Opciones del select para asignar nuevos hijos
    public static function get_opciones_modelo_relacionado($contab_reporte_id)
    {
        $vec['']='';
        $opciones = ContabCuentaGrupo::where('core_empresa_id',Auth::user()->empresa_id)->where('grupo_padre_id',0)->get();
        
        foreach ($opciones as $opcion)
        {
            $esta = DB::table('contab_reporte_tiene_grupos_cuentas')->where('contab_reporte_id',$contab_reporte_id)->where('contab_grupo_cuenta_id',$opcion->id)->get();
            
            if ( empty($esta) )
            {
                $vec[$opcion->id] = $opcion->descripcion;
            }
        }
        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'contab_reporte_tiene_grupos_cuentas';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'contab_reporte_id';
        $registro_modelo_hijo_id = 'contab_grupo_cuenta_id';

        return compact('nombre_tabla','nombre_columna1','registro_modelo_padre_id','registro_modelo_hijo_id');
    }

    
}
