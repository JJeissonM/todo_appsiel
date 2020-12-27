<?php

namespace App\Http\Controllers\PaginaWeb;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Input;
use View;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Core\Empresa;

use App\PaginaWeb\Pagina;
use App\PaginaWeb\Seccion;
use App\PaginaWeb\Articulo;

use App\PaginaWeb\Slug;

class SlugController extends Controller
{
    
    public function generar_slug( $cadena )
    {
        $slug_original = str_slug( $cadena );
        
        $slug_nuevo = $slug_original;

        $existe = true;
        $i = 2;
        while ( $existe )
        {
            $registro = Slug::where('slug', $slug_nuevo)->get()->first();

            if ( !is_null( $registro ) )
            {
                $slug_nuevo = $slug_original.'-'.$i;
                $i++;
            }else{
                $existe = false;
            }
        }
        
        return $slug_nuevo;
    }

}