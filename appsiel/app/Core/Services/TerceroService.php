<?php

namespace App\Core\Services;

use App\Core\Empresa;
use App\Core\Tercero;
use Illuminate\Support\Facades\Auth;

class TerceroService
{
    public function create_tercero($request)
    {        
        $tercero_enviado = Tercero::find((int)$request->core_tercero_id);

        if ($tercero_enviado == null) {
            $datos = $request->all();

            $datos['codigo_ciudad'] = '16920001'; // Valledupar
            $datos['tipo'] = 'Persona natural';
            $datos['id_tipo_documento_id'] = 13;  // CC
            $datos['descripcion'] = $request->name;
            $datos['nombre1'] = $request->name;

            $tercero_enviado = Tercero::create( $datos );
        }

        return $tercero_enviado;
    }

    public function create_or_update_tercero($request)
    {        
        $tercero_enviado = Tercero::find((int)$request->core_tercero_id);

        if ($tercero_enviado != null) {
            $datos = $request->all();

            $datos['descripcion'] = $request->name;
            $datos['nombre1'] = $request->name;

            $tercero_enviado->update( $datos );
        }else{
            $tercero_enviado = $this->create_tercero($request);
        }

        return $tercero_enviado;
    }
}