<?php

namespace App\Http\Controllers;

use App\Core\Empresa;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use Illuminate\Support\Facades\Auth;

class AyudaController extends Controller
{
    
    //muestra la vista de videos de ayuda
    public function videos()
    {
        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Sección de Ayuda']
        ];

        $modelo_empresa_id = 41;
        $aplicaciones = Aplicacion::where('estado', 'Activo')->orderBy('orden', 'ASC')->get();
        $empresa = Empresa::find(Auth::user()->empresa_id);
        $model_empresa = Modelo::find($modelo_empresa_id);
        $url = asset(config('configuracion.url_instancia_cliente') . '/storage/app/' . $model_empresa->ruta_storage_imagen . $empresa->imagen);
        $logo = $url . '?' . rand(1, 1000);
        //videos---------------------
        $videos = null;
        $generales = config('ayuda.videos.videos_generales');
        $apps = config('ayuda.videos.videos_apps');
        $total = count($generales);
        $arrayUrl = null;
        if ($total > 0) {
            foreach ($generales as $key => $value) {
                $arrayUrl[$key] = $value;
            }
            $videos['Generales'] = ['total' => $total,  'urls' => $arrayUrl];
        } else {
            $videos['Generales'] = ['total' => $total,  'urls' => $arrayUrl];
        }
        if (count($apps) > 0) {
            foreach ($aplicaciones as $value2) {
                foreach ($apps as $value3) {
                    $arrayUrl = null;
                    if ($value2->app == $value3['app']) {
                        $total2 = count($value3['urls']);
                        if ($total2 > 0) {
                            foreach ($value3['urls'] as $label => $url) {
                                $arrayUrl[$label] = $url;
                            }
                            $videos[$value2->descripcion] = ['total' => $total2, 'urls' => $arrayUrl];
                        } else {
                            $videos[$value2->descripcion] = ['total' => $total2, 'urls' => $arrayUrl];
                        }
                    }
                }
            }
        }
        //videos---------------------
        //pdfs---------------------
        $pdfs = null;
        $generales = config('ayuda.pdfs.pdfs_generales');
        $apps = config('ayuda.pdfs.pdfs_apps');
        $total = count($generales);
        $arrayUrl = null;
        if ($total > 0) {
            foreach ($generales as $key => $value) {
                $arrayUrl[$key] = $value;
            }
            $pdfs['Generales'] = ['total' => $total,  'urls' => $arrayUrl];
        } else {
            $pdfs['Generales'] = ['total' => $total,  'urls' => $arrayUrl];
        }
        if (count($apps) > 0) {
            foreach ($aplicaciones as $value2) {
                foreach ($apps as $value3) {
                    $arrayUrl = null;
                    if ($value2->app == $value3['app']) {
                        $total2 = count($value3['urls']);
                        if ($total2 > 0) {
                            foreach ($value3['urls'] as $label => $url) {
                                $arrayUrl[$label] = $url;
                            }
                            $pdfs[$value2->descripcion] = ['total' => $total2, 'urls' => $arrayUrl];
                        } else {
                            $pdfs[$value2->descripcion] = ['total' => $total2, 'urls' => $arrayUrl];
                        }
                    }
                }
            }
        }
        //pdfs---------------------
        return view('ayuda.videos', compact('aplicaciones', 'empresa', 'logo', 'videos','pdfs','miga_pan'));
    }
}
