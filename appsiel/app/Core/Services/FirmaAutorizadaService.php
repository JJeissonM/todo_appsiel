<?php

namespace App\Core\Services;

use App\Core\Empresa;
use App\Core\FirmaAutorizada;
use App\Core\Tercero;
use App\Sistema\Services\ImagenService;
use Illuminate\Support\Facades\Auth;

class FirmaAutorizadaService
{
    public function create( $request, $data )
    {
        $registro = FirmaAutorizada::create( $data );

        (new ImagenService())->almacenar_imagenes($request, 'firmas_autorizadas/', $registro);

        return $registro;
    }

    public function create_or_update($request,$data)
    {        
        $tercero_enviado = Tercero::find((int)$request->core_tercero_id);

        if ($tercero_enviado == null) {
            $registro = FirmaAutorizada::create( $data );
        }else{
            $registro = FirmaAutorizada::where( 'core_tercero_id', $tercero_enviado->id )->get()->first();
            if ( $registro == null ) {
                $registro = FirmaAutorizada::create( $data );
            }
        }

        (new ImagenService())->almacenar_imagenes($request, 'firmas_autorizadas/', $registro);

        return $registro;
    }
}