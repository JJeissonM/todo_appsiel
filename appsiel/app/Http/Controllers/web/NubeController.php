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
        $r = new Request();
        $breadc = [
            [
                'url' => 'NO',
                'etiqueta' => 'NUBE'
            ]
        ];
        $r->path = "./nube/";
        $this->listFolder($r);
        return view('web.nube.view')
            ->with('files', $this->rutas)
            ->with('miga_pan', $miga_pan)
            ->with('breadc', $breadc);
    }

    //pinta el file
    public function drawFile($extension)
    {
        $array = [
            'FOLDER' => 'folder-o',
            'txt' => 'file-archive-o',
            'pdf' => 'file-pdf-o',
            'docx' => 'file-word-o',
            'xls' => 'file-excel-o',

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
            'FOLDER' => 'yellow',
            'txt' => 'blue',
            'pdf' => 'red',
            'docx' => 'blue',
            'xls' => 'green',

        ];
        if (isset($array[$extension])) {
            return $array[$extension];
        } else {
            return "dim-gray";
        }
    }

    //lista el arbol de directorios y archivos
    public function listFolder(Request $request)
    {
        $path = $request->path;
        $this->rutas = null;
        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($path . $file) && $file != "." && $file != "..") {
                        $this->rutas[] = [
                            'file' => $file,
                            'path' => $path . $file,
                            'type' => 'FOLDER',
                            'file-size' => number_format(round(filesize($path . $file) / 1024 / 1024, 4), 2) . ' MB',
                            'extension' => 'FOLDER',
                            'icon' => $this->drawFile('FOLDER'),
                            'color' => $this->drawColor('FOLDER')
                        ];
                    } else {
                        if ($file != "." && $file != "..") {
                            $trozos = explode(".", $file);
                            $extension = end($trozos);
                            $this->rutas[] = [
                                'file' => $file,
                                'path' => $path . $file,
                                'type' => 'FILE',
                                'file-size' => number_format(round(filesize($path . $file) / 1024 / 1024, 4), 2) . ' MB',
                                'extension' => $extension,
                                'icon' => $this->drawFile($extension),
                                'color' => $this->drawColor($extension)
                            ];
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
}
