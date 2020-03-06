<?php

namespace App\Http\Controllers\web;

use App\PaginaWeb\Carousel;
use App\web\Album;
use App\web\Foto;
use App\web\Galeria;
use App\web\Navegacion;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

use App\web\RedesSociales;
use App\web\Footer;

class GaleriaController extends Controller
{
    public function create($widget)
    {

        $galeria = Galeria::where('widget_id', $widget)->first();
        if ($galeria == null) {
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
        } else {
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
                    'url' => 'seccion/' . $galeria->widget_id . '?id=' . Input::get('id'),
                    'etiqueta' => 'Galeria de Imagenes'
                ],
                [
                    'url' => 'NO',
                    'etiqueta' => 'Crear Álbum'
                ]
            ];
        }
        $variables_url = '?id=' . Input::get('id');
        return view('web.components.galeria.create', compact('miga_pan', 'variables_url', 'galeria', 'widget'));
    }

    public function guardarseccion(Request $request)
    {
        $galeria = new Galeria($request->all());
        $galeria->titulo = strtoupper($request->titulo);
        $result = $galeria->save();
        if ($result) {
            $message = 'La sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue almacenada de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }

    public function modificarseccion(Request $request, $id)
    {
        $galeria = Galeria::find($id);
        $galeria->titulo = strtoupper($request->titulo);
        $result = $galeria->save();
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

    public function destroy($id)
    {
        $galeria = Galeria::find($id);
        $widget = $galeria->widget_id;
        $result = $galeria->delete();
        if ($result) {
            $message = 'La galeria fue eliminada de correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La galeria no fue eliminada de forma correcta.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    public function store(Request $request)
    {
        $galeria = Galeria::where('widget_id', $request->widget_id)->first();
        $album = new Album();
        $album->titulo = strtoupper($request->titulo);
        $album->descripcion = $request->descripcion;
        $album->galeria_id = $galeria->id;
        $result = $album->save();
        $response = null;
        if ($result) {
            if (isset($request->imagen)) {
                foreach ($request->imagen as $value) {
                    if ($value->getSize() < 2097152) {
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
                        $response = $response . "<p>El archivo fue almacenado correctamente " . $file->getClientOriginalName() . "  <i class='fa fa-check'></i></p>";
                    } else {
                        $response = $response . "<p>El archivo no fue almacenado " . $value->getClientOriginalName() . "  <i class='fa fa-warning'></i> El tamaño del archivo excedia lo permitido (2MB)</p>";
                    }
                }
            }
            $message = "<h3>El Álbum fue almacenado correctamente.</h3>" . $response;
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El Álbum no fue almacenado correctamente, intente mas tarde.';
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
        $response = null;
        if ($result) {
            if ($request->hasFile('imagen')) {
                //if (isset($request->imagen)) {
                foreach ($request->imagen as $value) {
                    if ($value->getSize() < 2097152) {
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
                        $response = $response . "<p>El archivo fue almacenado correctamente " . $file->getClientOriginalName() . "  <i class='fa fa-check'></i></p>";
                    } else {
                        $response = $response . "<p>El archivo no fue almacenado " . $value->getClientOriginalName() . "  <i class='fa fa-warning'></i> El tamaño del archivo excedia lo permitido (2MB)</p>";
                    }
                }
            }
            $message = "<h3>El Álbum fue modificado correctamente.</h3>" . $response;
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
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
        if ($result) {
            $message = 'El Álbum fue eliminado correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El Álbum no fue eliminado de forma correcta.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    //albums
    public function albums($id)
    {
        $empresa = Galeria::find($id);
        $data = "";
        $data = "<section id='portfolio'><div class='container'><div class='text-center'>" . "<ul class='portfolio-filter'>";
        if (count($empresa->albums) > 0) {
            $data = $data . "<li><a href='#' data-filter='*' class='active'>TODOS</a></li>";
            foreach ($empresa->albums as $n) {
                $data = $data . "<li><a href='#' data-filter='." . str_slug($n->titulo) . "'>" . $n->titulo . "</a></li>";
            }
        }
        $data = $data . "</ul></div>";
        $data = $data . "<div class='portfolio-items isotope' style='position: relative; overflow: hidden; height: 260px;'>";
        if (count($empresa->albums) > 0) {
            foreach ($empresa->albums as $album) {
                if (count($album->fotos) > 0) {
                    foreach ($album->fotos as $foto) {
                        $data = $data . "<div class='portfolio-item " . str_slug($album->titulo) . " isotope-item' style='position: absolute; left: 0px; top: 0px; transform: translate3d(0px, 0px, 0px);'>";
                        $data = $data . "<div class='portfolio-item-inner'>
                                    <img class='img-responsive' style=\"height: 250px; width: 250px; object-fit: cover;\" src='" . url($foto->nombre) . "' alt=''>
                                    <div class='portfolio-info'>";
                        $data = $data . " <h3>$album->titulo</h3>
                                        $foto->nombre
                                        <a class='preview' href='" . url($foto->nombre) . "' rel='prettyPhoto'><i class='fa fa-eye'></i></a>
                                    </div>
                                </div>
                            </div>";
                    }
                }
            }
        }
        $data = $data . "</div></div></section>";

        $redes = RedesSociales::all();
        $footer = Footer::all()->first();
        $nav = Navegacion::all()->first();

        return view('web.container')
            ->with('e', $empresa)
            ->with('data', $data)
            ->with('redes', $redes)
            ->with('footer', $footer)
            ->with('title', 'GALERÍA')
            ->with('slogan1', 'Nuestra labor y la ejecución de eventos que genera experiencias que queremos contarte.')
            ->with('slogan2', 'Conoce la experiencia a través de fotos y videos.')
            ->with('nav',$nav);
    }

    public function importar()
    {
        dd("no puede");
        $datos = Carousel::all();
        $galeria = Galeria::find(3);
        foreach ($datos as $item) {
            $album = new Album();
            $album->titulo = $item->descripcion;
            $album->descripcion = $item->descripcion;
            $album->galeria_id = $galeria->id;
            $result = $album->save();
            if ($result) {
                $imagenes = json_decode($item->imagenes);
                foreach ($imagenes as $i) {
                    $foto = new Foto();
                    $foto->nombre = "img/" . $i->imagen;
                    $foto->album_id = $album->id;
                    $foto->save();
                }
            }
        }
    }
}
