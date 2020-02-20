<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

use DB;

use App\PaginaWeb\Seccion;

class PaginaTieneSeccion extends Model
{
	protected $table = 'pw_pagina_tiene_seccion';

    protected $fillable = ['orden','pagina_id','seccion_id'];


    public static function get_secciones_hijas_pagina( $pagina_id )
    {
    	$secciones_pagina = [];
    	
    	$secciones = PaginaTieneSeccion::where('pagina_id', $pagina_id)->get();

    	$i = 0;
    	foreach ($secciones as $fila)
    	{
    		$secciones_hijas = Seccion::where( 'padre_id', $fila->seccion_id )->get();

    		if ( !empty($secciones_hijas->toArray() ) )
    		{
    			foreach ($secciones_hijas as $hija)
    			{
    				$secciones_pagina[$i] = $hija;
    				$i++;
    			}
    		}else{
    			// La sección no tiene hijas
    			$secciones_pagina[$i] = Seccion::find( $fila->seccion_id );
    			$i++;
    		}
    	}

    	return $secciones_pagina;
    }

}
