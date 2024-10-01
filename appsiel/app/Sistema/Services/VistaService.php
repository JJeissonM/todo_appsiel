<?php

namespace App\Sistema\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VistaService
{
    
    /**
     * Para obtener/formatear las opciones de los campos tipo select y bsCheckBox
     */
    public static function get_opciones_campo_tipo_select( array $campo )
    {   
        $texto_opciones = '';
        if ( is_string( $campo['opciones'] ) )
        {
            $texto_opciones = trim($campo['opciones']);
        }

        if ( is_array( $campo['opciones'] ) ) {
            return $campo['opciones'];
        }
        
        $vec['']='';

        if ( $texto_opciones == '')
        {
            return $vec;
        }

        switch (substr($texto_opciones,0,strpos($texto_opciones, '_'))) {
            case 'table':
                // Cuando en opciones está la cadena table_[nombre_tabla_bd]
                $tabla = substr($texto_opciones,6,strlen($texto_opciones)-1);
            
                $opciones = DB::table($tabla)->get();
                
                // Mostar solo los registros de la empresa del usuario, si aplica
                if ( isset($opciones[0]->core_empresa_id) ) {
                    unset($opciones);
                    $opciones = DB::table($tabla)->where('core_empresa_id',Auth::user()->empresa_id)->get();
                }
                
                foreach ($opciones as $opcion){

                    // Si la tabla TIENE un campo descripcion para llenar el select
                    if ( isset($opcion->descripcion) ) {                        
                        $vec[$opcion->id] = $opcion->descripcion;
                    }

                    // Para la tabla roles
                    if (isset($opcion->name)) {
                        $vec[$opcion->id]=$opcion->name;
                    }

                    // Para la tabla estudiantes
                    if (isset($opcion->nombres)) {
                        $vec[$opcion->id]=$opcion->apellido1.' '.$opcion->apellido2.' '.$opcion->nombres;
                    }
                }

                // Para propiedad horizontal
                if( $campo['name'] == 'ph_propiedad_id')
                {
                    $opciones = DB::table($tabla)->leftJoin('core_terceros','core_terceros.id','=',$tabla.'.core_tercero_id')->where($tabla.'.core_empresa_id',Auth::user()->empresa_id)->select('core_terceros.id as core_tercero_id',$tabla.'.id',$tabla.'.codigo','core_terceros.descripcion')->get();

                    foreach ($opciones as $opcion)
                    {
                        $vec[$opcion->core_tercero_id.'a3p0'.$opcion->id] = $opcion->codigo.' - '.$opcion->descripcion;
                    }
                }

                if( $campo['name'] == 'escala_valoracion_id')
                {
                    foreach ($opciones as $opcion)
                    {
                        $vec[$opcion->id] = $opcion->nombre_escala." (".$opcion->calificacion_minima."-".$opcion->calificacion_maxima.")";
                    }
                }

                break;

            case 'model':

                // Cuando en opciones está la cadena model_[name_space_modelo]

                $model = substr($texto_opciones,6,strlen($texto_opciones)-1);

                $vec = app($model)->opciones_campo_select();

                break;

            case 'ayuda':
                
                break;
            
            default:

                // Cuando en opciones está la cadena en formato JSON
                $vec = json_decode($texto_opciones,true);
                
                if ( is_null($vec) )
                {
                    $vec = ['Error en formato JSON del campo.'];
                }

                break;
        }

        return $vec;
    }
}