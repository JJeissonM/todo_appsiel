<?php

namespace App\Sistema;

use Spatie\Permission\Models\Role;

use Input;
use DB;

class Perfil extends Role
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public $guarded = ['id'];

    protected $fillable = ['name'];

    public $encabezado_tabla = [ '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'ID', 'Descripción'];

    public $urls_acciones = '{"create":"core/roles/create","edit":"core/roles/id_fila/edit"}';

    // METODO PARA LA VISTA INDEX
    public static function consultar_registros($nro_registros, $search)
    {
        return Perfil::select(
                'roles.id AS campo1',
                'roles.name AS campo2',
                'roles.id AS campo3'
            )
            ->where("roles.name", "LIKE", "%$search%")
            ->orderBy('roles.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Perfil::select(
                'roles.id AS ID',
                'roles.name AS DESCRIPCION'
            )
            ->where("roles.name", "LIKE", "%$search%")
            ->orderBy('roles.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PERFILES";
    }

    public static function opciones_campo_select()
    {
        $opciones = Role::where('name', '<>', 'SuperAdmin')->orderBy('name')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->name;
        }

        return $vec;
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Campo
    */
    public static function get_tabla($registro_modelo_padre,$registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="myTable">
                    <thead>';
                        $encabezado_tabla = ['ID','App','Modelo','Nombre','Descripción (Menú)','URL','Menú padre','Ordén','habilitado','Icono','Acción'];
                        for($i=0;$i<count($encabezado_tabla);$i++){
                            $tabla.='<th>'.$encabezado_tabla[$i].'</th>';
                        }
                $tabla.='</thead>
                    <tbody>';
                        foreach($registros_asignados as $fila){

                            $tabla.='<tr>';
                                $tabla.='<td>'.$fila['id'].'</td>';
                                $tabla.='<td>'.$fila['core_app_id'].'</td>';
                                $tabla.='<td>'.$fila['modelo_id'].'</td>';
                                $tabla.='<td>'.$fila['name'].'</td>';
                                $tabla.='<td>'.$fila['descripcion'].'</td>';
                                $tabla.='<td>'.$fila['url'].'</td>';
                                $tabla.='<td>'.$fila['parent'].'</td>';
                                $tabla.='<td>'.$fila['orden'].'</td>';
                                $tabla.='<td>'.$fila['enabled'].'</td>';
                                $tabla.='<td>'.$fila['fa_icon'].'</td>';
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

    public static function get_opciones_modelo_relacionado($role_id)
    {
        $vec['']='';
        $opciones = DB::table('permissions')->orderBy('core_app_id')->get();
        foreach ($opciones as $opcion){
            $esta = DB::table('role_has_permissions')->where('role_id',$role_id)->where('permission_id',$opcion->id)->get();
            if ( empty($esta) )
            {
                $vec[$opcion->id]=$opcion->descripcion.' ('.$opcion->name.')';
            }
        }

        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'role_has_permissions';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'role_id';
        $registro_modelo_hijo_id = 'permission_id';

        return compact('nombre_tabla','nombre_columna1','registro_modelo_padre_id','registro_modelo_hijo_id');
    }
}
