<?php

namespace App\Http\Controllers\web;

use App\web\Album;
use App\web\Foto;
use App\web\Galeria;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class GaleriaController extends Controller
{
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
                'url' => 'NO',
                'etiqueta' => 'Galeria de Imagenes'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $galeria = Galeria::where('widget_id', $widget)->first();
        return view('web.components.galeria.create', compact('miga_pan', 'variables_url', 'galeria', 'widget'));
    }

    public function store(Request $request)
    {
        $galeria = Galeria::where('widget_id', $request->widget_id)->first();
        if ($galeria == null) {
            $galeria = new Galeria($request->all());
        }
        if ($galeria->save()) {
            $album = new Album();
            $album->titulo = strtoupper($request->titulo);
            $album->descripcion = $request->descripcion;
            $album->galeria_id = $galeria->id;
            $result = $album->save();
            if ($result) {
                if (isset($request->imagen)) {
                    foreach ($request->imagen as $value) {
                        $foto = new Foto();
                        $foto->album_id = $album->id;
                        $file = $value;
                        $name = time() . $file->getClientOriginalName();
                        $filename = "img/" . $name;
                        $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
                        if ($flag !== false) {
                            $foto->fill(['nombre' => $filename]);
                        }
                        $foto->save();
                    }
                }
                $message = 'El Álbum fue almacenado correctamente.';
                $variables_url = '?id=' . Input::get('id');
                return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
            } else {
                $message = 'El Álbum no fue almacenado correctamente, intente mas tarde.';
                $variables_url = '?id=' . Input::get('id');
                return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
            }
        } else {
            $message = 'La Galeria no fue almacenada correctamente, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    public function edit($album_id)
    {
        $album = Album::find($album_id);
        $fotos = $album->fotos;
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
                'url' => 'seccion/' . $album->galeria->widget_id . '?id=' . Input::get('id'),
                'etiqueta' => 'Galeria de Imagenes'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Editar Álbum'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $widget = $album->galeria->widget_id;
        return view('web.components.galeria.edit', compact('miga_pan', 'variables_url', 'album', 'widget'));

    }

    public function updated(Request $request, $id)
    {
        $album = Album::find($id);
        $album->titulo = strtoupper($request->titulo);
        $album->descripcion = $request->descripcion;
        $result = $album->save();
        if ($result) {
            if (isset($request->imagen)) {
                foreach ($request->imagen as $value) {
                    $foto = new Foto();
                    $foto->album_id = $album->id;
                    $file = $value;
                    $name = time() . $file->getClientOriginalName();
                    $filename = "img/" . $name;
                    $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
                    if ($flag !== false) {
                        $foto->fill(['nombre' => $filename]);
                    }
                    $foto->save();
                }
                $message = 'El Álbum fue modificado correctamente.';
                $variables_url = '?id=' . Input::get('id');
                return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
            }
        } else {
            $message = 'El Álbum no fue modificado correctamente, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }

    public function destroyImg($img)
    {
        $imagen = Foto::find($img);
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
                'url' => 'seccion/' . $imagen->album->galeria->widget_id . '?id=' . Input::get('id'),
                'etiqueta' => 'Galeria de Imagenes'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Editar Álbum'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $widget = $imagen->album->galeria->widget_id;
        $album = $imagen->album;
        $result = $imagen->delete();
        if ($result) {
            unlink($imagen->nombre);
            $message = 'Imagen eliminada de forma exitosa.';
            return redirect(url('galeria/edit/' . $album->id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La imagen no pudo ser eliminada.';
            return redirect(url('galeria/edit/' . $album->id) . $variables_url)->with('flash_message', $message);
        }
    }

    public function destroyAlbum($id)
    {
        $album = Album::find($id);
        $widget = $album->galeria->widget_id;
        $fotos = $album->fotos;
        if (count($fotos) > 0) {
            foreach ($fotos as $img) {
                unlink($img->nombre);
            }
        }
        $result = $album->delete();
        if($result){
            $message = 'El Álbum fue eliminado correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }else{
            $message = 'El Álbum no fue eliminado de forma correcta.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

}
