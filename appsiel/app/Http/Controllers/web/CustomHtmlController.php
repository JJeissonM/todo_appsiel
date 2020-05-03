<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;

use App\web\CustomHtml;


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

}
