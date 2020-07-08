<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\web\Icon;
use Illuminate\Support\Facades\Input;

class NubeController extends Controller
{

    protected $rutas = null;


    //index
    public function view()
    {
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Almacenamiento en la Nube'
            ]
        ];
        $this->listFolder('./nube/');
        $simple = "'";
        $doble = '"';
        $token = str_replace($doble, $simple, csrf_field());
        return view('web.nube.view')
            ->with('files', $this->rutas)
            ->with('miga_pan', $miga_pan)
            ->with('prev', 'NO')
            ->with('path', './nube/')
            ->with('token', $token);
    }

    //pinta el file
    public function drawFile($extension)
    {
        $array = [
            'FOLDER' => 'folder-o',
            'txt' => 'file-text',
            'pdf' => 'file-pdf-o',
            'docx' => 'file-word-o',
            'xls' => 'file-excel-o',
            'xlsx' => 'file-excel-o',
            'psd' => 'file-powerpoint-o',
            'exe' => 'bars',
            'jpg' => 'picture-o',
            'jpeg' => 'file-image-o',
            'mp4' => 'video-camera',
            'png' => 'picture-o',
            'rar' => 'file-archive-o',
            'zip' => 'file-archive-o',
            'mp3' => 'file-audio-o',
            'html' => 'chrome'
        ];
        if (isset($array[$extension])) {
            return $array[$extension];
        } else {
            return "file-o";
        }
    }

    //pinta el color
    public function drawColor($extension)
    {
        $array = [
            'FOLDER' => '#ffca08',
            'txt' => '#3183d8',
            'pdf' => '#d83131',
            'docx' => '#0b278c',
            'xls' => '#2a864a',
            'xlsx' => '#2a864a',
            'psd' => '#481d6f',
            'exe' => '#3a866e',
            'jpg' => '#801c73',
            'jpeg' => '#801c73',
            'mp4' => '#285648',
            'png' => '#801c73',
            'rar' => '#a15dd4',
            'zip' => '#a15dd4',
            'mp3' => '#2e8cd0',
            'html' => '#c70039'
        ];
        if (isset($array[$extension])) {
            return $array[$extension];
        } else {
            return "dim-gray";
        }
    }

    //lista el arbol de directorios y archivos
    public function listFolder($path)
    {
        $this->rutas = null;
        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                $m = 0;
                while (($file = readdir($dh)) !== false) {
                    $m = $m + 1;
                    if (is_dir($path . $file) && $file != "." && $file != "..") {
                        $this->rutas[] = [
                            'file' => $file,
                            'path' => $path . $file,
                            'type' => 'FOLDER',
                            'tamanio' => number_format(round(filesize($path . $file) / 1024 / 1024, 4), 2) . ' MB',
                            'extension' => 'FOLDER',
                            'icon' => $this->drawFile('FOLDER'),
                            'color' => $this->drawColor('FOLDER'),
                            'm' => 'FORM_' . $m
                        ];
                    } else {
                        if ($file != "." && $file != "..") {
                            $trozos = explode(".", $file);
                            $extension = end($trozos);
                            $this->rutas[] = [
                                'file' => $file,
                                'path' => $path . $file,
                                'type' => 'FILE',
                                'tamanio' => number_format(round(filesize($path . $file) / 1024 / 1024, 4), 2) . ' MB',
                                'extension' => $extension,
                                'icon' => $this->drawFile($extension),
                                'color' => $this->drawColor($extension),
                                'm' => 'FORM_' . $m
                            ];
                        }
                    }
                }
                closedir($dh);
            }
        }
        if ($this->rutas != null) {
            $this->rutas = $this->orderMultiDimensionalArray($this->rutas, 'type', true);
        }
    }

    //ordena arreglos multidimencionales
    public function orderMultiDimensionalArray($toOrderArray, $field, $inverse = false)
    {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
            $position[$key] = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }


    //ejecuta ruta
    public function listPath(Request $request)
    {
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . $request->id,
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Almacenamiento en la Nube'
            ]
        ];
        $prev = $this->getPrevious($request->path);
        $path = $request->path;
        if ($prev == './') {
            $prev = "NO";
            $path = "./nube/";
        }
        $this->listFolder($request->path);
        $simple = "'";
        $doble = '"';
        $token = str_replace($doble, $simple, csrf_field());
        return view('web.nube.view')
            ->with('files', $this->rutas)
            ->with('miga_pan', $miga_pan)
            ->with('prev', $prev)
            ->with('path', $path)
            ->with('token', $token);
    }

    //calcula prev
    public function getPrevious($path)
    {
        $array = explode('/', $path);
        $total = count($array);
        $ruta = "";
        for ($i = 0; $i < $total - 2; $i++) {
            $ruta = $ruta . $array[$i] . "/";
        }
        return $ruta;
    }

    //delete file
    public function delete(Request $request)
    {
        if ($request->type == 'FOLDER') {
            $this->rmDir_rf($request->file_id);
        } else {
            unlink($request->file_id);
        }
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . $request->id,
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Almacenamiento en la Nube'
            ]
        ];
        $this->listFolder($request->path);
        $simple = "'";
        $doble = '"';
        $token = str_replace($doble, $simple, csrf_field());
        return view('web.nube.view')
            ->with('files', $this->rutas)
            ->with('miga_pan', $miga_pan)
            ->with('prev', $request->prev)
            ->with('path', $request->path)
            ->with('token', $token);
    }

    //Borra directorio
    function rmDir_rf($carpeta)
    {
        foreach (glob($carpeta . "/*") as $archivos_carpeta) {
            if (is_dir($archivos_carpeta)) {
                $this->rmDir_rf($archivos_carpeta);
            } else {
                unlink($archivos_carpeta);
            }
        }
        rmdir($carpeta);
    }

    //crea nueva carpeta
    public function nueva(Request $request)
    {
        mkdir($request->path . $request->name, 0700);
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . $request->id,
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Almacenamiento en la Nube'
            ]
        ];
        $this->listFolder($request->path);
        $simple = "'";
        $doble = '"';
        $token = str_replace($doble, $simple, csrf_field());
        return view('web.nube.view')
            ->with('files', $this->rutas)
            ->with('miga_pan', $miga_pan)
            ->with('prev', $request->prev)
            ->with('path', $request->path)
            ->with('token', $token);
    }

    //subir archivos
    public function upload(Request $request)
    {
        if (isset($request->archivo)) {
            $files = $request->file("archivo");
            foreach ($files as $f) {
                $name = str_slug($f->getClientOriginalName()) . "." . $f->getClientOriginalExtension();
                $path = $request->path . $name;
                file_put_contents($path, file_get_contents($f->getRealPath()), LOCK_EX);
            }
        }
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . $request->id,
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Almacenamiento en la Nube'
            ]
        ];
        $this->listFolder($request->path);
        $simple = "'";
        $doble = '"';
        $token = str_replace($doble, $simple, csrf_field());
        return view('web.nube.view')
            ->with('files', $this->rutas)
            ->with('miga_pan', $miga_pan)
            ->with('prev', $request->prev)
            ->with('path', $request->path)
            ->with('token', $token);
    }
}
