<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;

use App\web\CustomHtml;
use App\Core\Tercero;


class CustomHtmlController extends Controller
{
    
    public function store(Request $request)
    {
        $registro = CustomHtml::create( $request->all() );

        return redirect( 'seccion/' . $request->widget_id . '?id=' . $request->url_id)->with('flash_message', 'Sección almacenada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $registro = CustomHtml::find( $id );
        $registro->fill( $request->all() );
        $registro->save();

        return redirect( 'seccion/' . $request->widget_id . '?id=' . $request->url_id)->with('flash_message', 'Sección actualizada correctamente.');

    }

    public function formulario_campana( Request $request )
    {

        $tipo = 'Persona natural';
        $codigo_ciudad = 16920001; // Valledupar
        $numero_identificacion = uniqid();

        $tercero = Tercero::create( array_merge( $request->all(),
                                    [   'codigo_ciudad' => $codigo_ciudad, 
                                        'core_empresa_id' => 1, 
                                        'numero_identificacion' => $numero_identificacion, 
                                        'descripcion' => $request->apellido1." ".$request->nombre1, 
                                        'tipo' => $tipo, 
                                        'estado' => 'Activo', 
                                        'creado_por' => 'formulario_campana'] ) );

        return redirect( $request->url_destino.'?nombre='.$request->nombre1 );
    }

}