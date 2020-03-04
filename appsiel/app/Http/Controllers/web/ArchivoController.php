<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\web\Archivo;
use App\web\Archivoitem;

class ArchivoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $a = new Archivo($request->all());
        $result = $a->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser almacenada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $as = Archivo::find($id);
        $as->fill($request->all());
        $result = $as->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue modificada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser modificada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $a = Archivoitem::find($request->id);
        $result = $a->delete();
        $variables_url = $request->variables_url;
        if ($result) {
            unlink('docs/' . $a->file);
            $message = 'El archivo fue borrado correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El archivo no pudo ser borrado.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function archivostore(Request $request)
    {
        $variables_url = $request->variables_url;
        if (isset($request->archivo)) {
            $file = $request->file("archivo");
            $a = new Archivoitem($request->all());
            //$name = "Archivo_" . $date['year'] . $date['mon'] . $date['mday'] . $date['hours'] . $date['minutes'] . $date['seconds'] . "." . $f->getClientOriginalExtension();
            $name = str_slug($file->getClientOriginalName()) . '-' . time() . '.' . $file->clientExtension();
            $path = "docs/" . $name;
            $flag = file_put_contents($path, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $a->file = $name;
                if ($a->save()) {
                    return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', "Archivo almacenado con éxito");
                }
            }
        } else {
            $message = 'No ha seleccionado archivos.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }
}
