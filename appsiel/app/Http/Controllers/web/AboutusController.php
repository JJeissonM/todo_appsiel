<?php

namespace App\Http\Controllers\web;

use App\web\Aboutus;
use App\web\Icon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

use App\web\RedesSociales;
use App\web\Footer;

class AboutusController extends Controller
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
                'etiqueta' => 'About Us'
            ]
        ];
        $iconos = Icon::all();
        $variables_url = '?id=' . Input::get('id');
        $aboutus = Aboutus::where('widget_id', $widget)->first();
        return view('web.components.about_us.create', compact('miga_pan', 'variables_url', 'aboutus', 'iconos', 'widget'));
    }

    public function store(Request $request)
    {
        $aboutus = new Aboutus($request->all());

        if ($request->hasFile('imagen')) {

            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();

            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $aboutus->fill(['imagen' => $filename]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }

        }
        $result = $aboutus->save();
        if ($result) {
            $message = 'About us almacenado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'About us no fue almacenado correctamente, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }

    }

    public function updated(Request $request, $id)
    {
        $aboutus = Aboutus::find($id);
        $img = $aboutus->imagen;
        $aboutus->fill($request->all());
        if ($request->hasFile('imagen')) {

            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();

            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                // $bool = unlink($img);
                $aboutus->fill(['imagen' => url($filename)]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $result = $aboutus->save();
        if ($result) {
            $message = 'About us modificado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'About us no pudo se modificado de forma correcta, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }

    }

    //Leer institucional
    public function leer_institucional($id)
    {
        $empresa = Aboutus::find($id);
        $data = "<h2 class='section-title text-center wow fadeInDown'>MISIÓN</h2>";
        if ($empresa->mision != null) {
            $data = $data . "<div class='col-sm-12'>"
                . "<div class='media service-box wow fadeInRight'>"
                . "<div class='media-body'><p>" . $empresa->mision . "</p></div>"
                . "</div></div>";
        }
        $data = $data . "<h2 class='section-title text-center wow fadeInDown'>VISIÓN</h2>";
        if ($empresa->vision != null) {
            $data = $data . "<div class='col-sm-12'>"
                . "<div class='media service-box wow fadeInRight'>"
                . "<div class='media-body'><p>" . $empresa->vision . "</p></div>"
                . "</div></div>";
        }
//        $valores = $empresa->valors;
        $data = $data . "<h2 class='section-title text-center wow fadeInDown'>VALORES</h2>";
        if ($empresa->valores != null) {
//            $data = $data . "<div class='col-sm-12 wow fadeInRight'><h2 class='section-title text-center wow fadeInDown'>VALORES</h2>"
//                . "<div class='row'><div class='col-sm-12'><ul class='nostyle'>";
//            foreach ($valores as $v) {
//                $data = $data . "<li><i class='fa fa-check-square'></i> " . $v->valor . "</li>";
//            }
//            $data = $data . "</ul></div></div></div>";
            $data = $data . "<div class='col-sm-12'>"
                . "<div class='media service-box wow fadeInRight'>"
                . "<div class='media-body'><p>" . $empresa->valores . "</p></div>"
                . "</div></div>";
        }
//        $resenias = $empresa->resenias;
        $data = $data . "<h2 class='section-title text-center wow fadeInDown'>RESEÑA HISTORICA</h2>";
        if ($empresa->resenia != null) {
            $data = $data . "<div class='col-sm-12'>"
                . "<div class='media service-box wow fadeInRight'>"
                . "<div class='media-body'><p>" . $empresa->resenia. "</p></div>"
                . "</div></div>";
//            $data = $data . "<div class='col-md-12 wow fadeInRight'><h2 class='section-title text-center wow fadeInDown'>" . $resenias[0]->titulo . "</h2>"
////                . $resenias[0]->cuerpo . "<h2>Galería de la Reseña</h2></div><div class='col-md-12'>";
////            foreach ($resenias[0]->imagenes as $imagen) {
////                $data = $data . "<div class='col-md-3'>"
////                    . "<a target='_blank' href='" . asset('images/resenia/' . $imagen->imagen) . "'>"
////                    . "<img style='width: 100%;' src='" . asset('images/resenia/' . $imagen->imagen) . "' alt='" . $imagen->imagen . "'></a></div>";
////            }
//            $data = $data . "</div>";
        }


        $redes = RedesSociales::all();
        $footer = Footer::all()->first();

        return view('web.container')
            ->with('e', $empresa)
            ->with('data', $data)
            ->with('redes', $redes)
            ->with('footer', $footer)
            ->with('title', 'INSTITUCIONAL')
            ->with('slogan1', $empresa->descripcion)
            ->with('slogan2', '');
    }
}
