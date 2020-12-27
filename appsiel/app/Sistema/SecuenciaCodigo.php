<?php

namespace App\Sistema;

use Illuminate\Database\Eloquent\Model;

use App\Matriculas\Grado;
use Auth;

// Para gestionar los concecutivos (código) de algunos módulos
class SecuenciaCodigo extends Model
{
    protected $table = 'sys_secuencias_codigos';

    protected $fillable = ['id_colegio','modulo','consecutivo','anio','estructura_secuencia','estado'];

    public $encabezado_tabla = ['Módulo','Consecutivo actual','Año (AA)','Estrucutura secuencia','Estado','Acción'];

    public static function consultar_registros()
    {
        $registros = SecuenciaCodigo::select('sys_secuencias_codigos.modulo AS campo1','sys_secuencias_codigos.consecutivo AS campo2','sys_secuencias_codigos.anio AS campo3','sys_secuencias_codigos.estructura_secuencia AS campo4','sys_secuencias_codigos.estado AS campo5','sys_secuencias_codigos.id AS campo6')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function get_codigo( $modulo, $otros_datos = null )
    {
    	// Generar el código de la matrícula
        $largo_consecutivo = 3;
        $secuencia = SecuenciaCodigo::where( ['modulo'=>$modulo, 'estado'=>'Activo' ] )->get()->first();
        $consecutivo = $secuencia->consecutivo + 1;
        $largo = strlen($consecutivo);

        switch ( $secuencia->estructura_secuencia ) {
            case '(anio)-(consecutivo)':
                $codigo = $secuencia->anio.'-'.str_repeat('0', $largo_consecutivo-$largo).$consecutivo;
                break;
            
            case '(consecutivo)':
                $codigo = $consecutivo;
                break;

            case '(anio)(consecutivo)-(grado)':

                $grado = Grado::find($otros_datos->grado_id);
                $codigo = $secuencia->anio.str_repeat('0', $largo_consecutivo-$largo).$consecutivo.'-'.$grado->codigo;
                break;
            
            default:
                $codigo = $consecutivo;
                break;
        }

        return $codigo;
    }

    public static function incrementar_consecutivo( $modulo )
    {
        SecuenciaCodigo::where( [ 'modulo'=> $modulo, 'estado'=>'Activo'] )->increment('consecutivo');
    }
}
