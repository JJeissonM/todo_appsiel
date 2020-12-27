<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\web\Team;
use App\web\Teamitem;
use Illuminate\Support\Facades\Input;

class TeamController extends Controller
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
    public function create($widget)
    {
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'paginas?id=' . Input::get('id'),
                'etiqueta' => 'Paginas y secciones'
            ],
            [
                'url' => 'seccion/' . $widget . '?id=' . Input::get('id'),
                'etiqueta' => 'Componente Team (Equipo de trabajo)'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Crear Tarjeta'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $team = Team::where('widget_id', $widget)->first();
        return view('web.components.teams.create', compact('miga_pan', 'variables_url', 'team', 'widget'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->tipo_fondo == '') {
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('mensaje_error', 'Debe indicar el tipo de fondo a usar en el componente.');
        }
        $team = new Team($request->all());
        if ($request->tipo_fondo == 'IMAGEN') {
            //el fondo es una imagen
            $file = $request->file('fondo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $team->fondo = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $team->title = strtoupper($request->title);
        $team->description = $request->description;
        $result = $team->save();
        if ($result) {
            $message = 'La sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue almacenada de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
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
        $item = Teamitem::find($id);
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'paginas?id=' . Input::get('id'),
                'etiqueta' => 'Paginas y secciones'
            ],
            [
                'url' => 'seccion/' . $item->team->widget_id . '?id=' . Input::get('id'),
                'etiqueta' => 'Componente Team (Equipo de trabajo)'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Editar Tarjeta'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $widget = $item->team->widget_id;
        $team = $item->team;
        return view('web.components.teams.edit', compact('miga_pan', 'team', 'variables_url', 'item', 'widget'));
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
        $team = Team::find($id);
        $team->title = strtoupper($request->title);
        $team->description = $request->description;
        $team->configuracionfuente_id=$request->configuracionfuente_id;
        $tipo_fondo = $team->tipo_fondo;
        if ($request->tipo_fondo == '') {
            $team->tipo_fondo = $tipo_fondo;
        }
        if ($request->tipo_fondo != '') {
            if ($request->tipo_fondo == 'IMAGEN') {
                if (isset($request->fondo)) {
                    //el fondo es una imagen
                    $file = $request->file('fondo');
                    $name = time() . $file->getClientOriginalName();
                    $filename = "img/" . $name;
                    $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
                    if ($flag !== false) {
                        $team->fondo = $filename;
                        $team->tipo_fondo = 'IMAGEN';
                        $team->repetir = $request->repetir;
                        $team->direccion = $request->direccion;
                    } else {
                        $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                        return redirect()->back()->withInput($request->input())
                            ->with('mensaje_error', $message);
                    }
                }
            } else {
                $team->fondo = $request->fondo;
                $team->tipo_fondo = "COLOR";
            }
        }
        $result = $team->save();
        if ($result) {
            $message = 'La sección fue modificada correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue modificada de forma correcta.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /*
     * Elimina un itemservicio
     * @param Itemservicio $id
     */
    public function destroy($id)
    {
        $item = Teamitem::find($id);
        $widget = $item->team->widget_id;
        $result = $item->delete();
        if ($result) {
            $message = 'Tarjeta eliminada correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La tarjeta no fue eliminada.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    /* Guarda los item de un team
     * @param $request
     */
    public function guardar(Request $request)
    {
        $variables_url = $request->variables_url;
        $item = new Teamitem($request->all());
        $item->title = strtoupper($request->title);
        if (isset($request->imagen)) {
            //la imagen de la tarjeta
            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $item->imagen = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        } else {
            $item->imagen = "";
        }
        $result = $item->save();
        if ($request) {
            $message = 'El ítem fue almacenado correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El ítem no fue almacenado de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /*
     * Modifica un Item
     * @param $request Item $id
     *
     */
    public function modificar(Request $request, $id)
    {
        $item = Teamitem::find($id);
        $imagen = $item->imagen;
        $item->title = strtoupper($request->title);
        $item->description = $request->description;
        $item->more_details = $request->more_details;
        $item->text_color = $request->text_color;
        $item->title_color = $request->title_color;
        $item->background_color = $request->background_color;
        if (isset($request->imagen)) {
            //el fondo es una imagen
            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $item->imagen = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        } else {
            $item->imagen = $imagen;
        }
        $result = $item->save();
        if ($result) {
            $message = 'La tarjeta fue modificada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            $message = 'La tarjeta no fue modificada de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }

    /*
     * Elimina toda la sección
     * @param Team $id
     */
    public function delete($id)
    {
        $team = Team::find($id);
        $widget = $team->widget_id;
        $result = $team->delete();
        if ($result) {
            $message = 'Sección eliminada con éxito';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue eliminada.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }
}
