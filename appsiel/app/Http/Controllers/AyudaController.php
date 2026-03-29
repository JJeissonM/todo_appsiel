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
                $arrayUrl[$key] = $this->prepararVideo($value);
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
                                $arrayUrl[$label] = $this->prepararVideo($url);
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

    protected function prepararVideo($video)
    {
        $video['player_type'] = 'video';
        $video['player_url'] = $video['url'];

        if ($this->esUrlYoutube($video['url'])) {
            $video['player_type'] = 'youtube';
            $video['player_url'] = $this->generarUrlEmbedYoutube($video['url']);
        }

        return $video;
    }

    protected function esUrlYoutube($url)
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (empty($host)) {
            return false;
        }

        $host = strtolower($host);

        return strpos($host, 'youtube.com') !== false || strpos($host, 'youtu.be') !== false;
    }

    protected function generarUrlEmbedYoutube($url)
    {
        $videoId = $this->obtenerIdVideoYoutube($url);

        if (empty($videoId)) {
            return $url;
        }

        return 'https://www.youtube.com/embed/' . $videoId . '?rel=0';
    }

    protected function obtenerIdVideoYoutube($url)
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $query = parse_url($url, PHP_URL_QUERY);

        if (strpos($host, 'youtu.be') !== false) {
            return $path;
        }

        parse_str($query, $queryParams);

        if (!empty($queryParams['v'])) {
            return $queryParams['v'];
        }

        $segments = explode('/', $path);
        $embedIndex = array_search('embed', $segments);

        if ($embedIndex !== false && isset($segments[$embedIndex + 1])) {
            return $segments[$embedIndex + 1];
        }

        $shortsIndex = array_search('shorts', $segments);

        if ($shortsIndex !== false && isset($segments[$shortsIndex + 1])) {
            return $segments[$shortsIndex + 1];
        }

        return null;
    }
}
