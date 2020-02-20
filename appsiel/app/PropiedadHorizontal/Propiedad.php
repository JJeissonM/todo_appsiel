<?php

namespace App\PropiedadHorizontal;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;


class Propiedad extends Model
{
    protected $table = 'ph_propiedades';

    protected $fillable = ['codigo','core_empresa_id','core_tercero_id','tipo_propiedad','nomenclatura','coeficiente_copropiedad','cxc_servicio_id','valor_cuota_defecto','cedula_arrendatario','nombre_arrendatario','telefono_arrendatario','email_arrendatario','estado','fecha_entrega','tipo_de_uso','parqueadero_asignado','deposito_asignado','numero_matricula_inmobiliaria','cuenta_ingresos_id'];

    public $encabezado_tabla = ['ID','Conjunto','Código','Doc. Propietario','Propietario','Tipo','Nomenclatura','Residente','Estado','Acción'];

    public static function consultar_registros()
    {

        $registros = Propiedad::leftJoin('core_empresas', 'core_empresas.id', '=', 'ph_propiedades.core_empresa_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'ph_propiedades.core_tercero_id')
                    ->where('ph_propiedades.core_empresa_id', Auth::user()->empresa_id)
                    ->select('ph_propiedades.id AS campo1','core_empresas.descripcion AS campo2','ph_propiedades.codigo AS campo3','core_terceros.numero_identificacion AS campo4','core_terceros.descripcion AS campo5','ph_propiedades.tipo_propiedad AS campo6','ph_propiedades.nomenclatura AS campo7','ph_propiedades.nombre_arrendatario AS campo8','ph_propiedades.estado AS campo9','ph_propiedades.id AS campo10')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function get_propiedades($core_empresa_id)
    {

        $propiedades = Propiedad::leftJoin('core_empresas', 'core_empresas.id', '=', 'ph_propiedades.core_empresa_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'ph_propiedades.core_tercero_id')
                    ->where('ph_propiedades.core_empresa_id',$core_empresa_id)
                    ->where('ph_propiedades.estado','Activo')
                    ->select('ph_propiedades.codigo','core_empresas.razon_social','ph_propiedades.tipo_propiedad','ph_propiedades.core_tercero_id','ph_propiedades.coeficiente_copropiedad','ph_propiedades.nombre_arrendatario','ph_propiedades.id','ph_propiedades.cxc_servicio_id','ph_propiedades.valor_cuota_defecto','ph_propiedades.cuenta_ingresos_id','core_terceros.descripcion AS descripcion')
                    ->get()
                    ->toArray();

        return $propiedades;
    }

    public function servicios()
    {
        return $this->belongsToMany('App\CxC\CxcServicio','ph_propiedad_tiene_servicios','propiedad_id','cxc_servicio_id');
    }




    public static function opciones_campo_select()
    {
        $opciones = DB::table('core_terceros')->leftJoin('ph_propiedades','ph_propiedades.core_tercero_id','=','core_terceros.id')->where('core_terceros.core_empresa_id',Auth::user()->empresa_id)->select('core_terceros.id as core_tercero_id','ph_propiedades.id','ph_propiedades.codigo','core_terceros.descripcion')->orderBy('ph_propiedades.codigo','ASC')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion)
        {
            if ( $opcion->codigo != '' ) 
            {
              $vec[$opcion->core_tercero_id.'a3p0'.$opcion->id] = $opcion->codigo.' - '.$opcion->descripcion;
            }else{
              $vec[$opcion->core_tercero_id.'a3p00'] = $opcion->descripcion;
            }            
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
                        $encabezado_tabla = ['Orden','ID','Descripción','Precio','Acción'];
                        for($i=0;$i<count($encabezado_tabla);$i++){
                            $tabla.='<th>'.$encabezado_tabla[$i].'</th>';
                        }
                $tabla.='</thead>
                    <tbody>';
                        foreach($registros_asignados as $fila)
                        {
                            $servicio = DB::table('ph_propiedad_tiene_servicios')
                                        ->where('propiedad_id', '=', $registro_modelo_padre->id)
                                        ->where('cxc_servicio_id', '=', $fila['id'])
                                        ->get()[0];

                            if ( $servicio->valor_servicio == 0) 
                            {
                                $precio_venta = $fila['precio_venta'];
                            }else{
                                $precio_venta = $servicio->valor_servicio;
                            }

                            $tabla.='<tr>';
                                $tabla.='<td>'.$servicio->orden.'</td>';
                                $tabla.='<td>'.$fila['id'].'</td>';
                                $tabla.='<td>'.$fila['descripcion'].'</td>';
                                $tabla.='<td>$'.number_format($precio_venta, 0, ',', '.').'</td>';
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

    public static function get_opciones_modelo_relacionado($propiedad_id)
    {
        $vec['']='';
        $opciones = DB::table('cxc_servicios')->where('cxc_servicios.core_empresa_id', Auth::user()->empresa_id)->get();

        foreach ($opciones as $opcion){
            $esta = DB::table('ph_propiedad_tiene_servicios')->where('propiedad_id',$propiedad_id)
            ->where('cxc_servicio_id',$opcion->id)
            ->get();

            if ( empty($esta) )
            {
                $vec[$opcion->id]=$opcion->descripcion.' ($'.number_format($opcion->precio_venta, 0, ',', '.').')';
            }
        }
        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'ph_propiedad_tiene_servicios';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'propiedad_id';
        $registro_modelo_hijo_id = 'cxc_servicio_id';

        return compact('nombre_tabla','nombre_columna1','registro_modelo_padre_id','registro_modelo_hijo_id');
    }
}
