<?php 

namespace App\Sistema\Services;

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

}