<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Sistema\Aplicacion;
use App\web\Icon;
use Illuminate\Support\Facades\Input;

class NubeController extends Controller
{
    protected $rutas = null;

    protected function getMigaPan($id)
    {
        $aplicacion = Aplicacion::where('id',Input::get('id'))->first();
        return [
            [
                'url' => $aplicacion->app.'?id='.$aplicacion->id,
                'etiqueta' => $aplicacion->descripcion
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Almacenamiento en la Nube'
            ]
        ];
    }

    protected function getCloudRoot()
    {
        $legacyRoot = rtrim(base_path('../nube'), DIRECTORY_SEPARATOR);
        $publicRoot = rtrim(public_path('nube'), DIRECTORY_SEPARATOR);
        $root = is_dir($legacyRoot) ? $legacyRoot : $publicRoot;

        if (!is_dir($root)) {
            mkdir($root, 0775, true);
        }

        return realpath($root);
    }

    protected function normalizeCloudPath($path)
    {
        $path = str_replace('\\', '/', (string) $path);
        $path = preg_replace('#^(\./)?nube/?#', '', $path);
        $path = trim($path, '/');

        if ($path == '.' || $path == '') {
            return '';
        }

        if (preg_match('#(^|/)\.\.(/|$)#', $path)) {
            throw new \InvalidArgumentException('Ruta no permitida.');
        }

        return $path;
    }

    protected function getFullCloudPath($path, $mustExist = false)
    {
        $root = $this->getCloudRoot();
        $relativePath = $this->normalizeCloudPath($path);
        $fullPath = $root . ($relativePath == '' ? '' : DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        $pathToCheck = $mustExist ? realpath($fullPath) : realpath(dirname($fullPath));

        if ($mustExist && $pathToCheck === false) {
            throw new \InvalidArgumentException('El archivo o carpeta no existe.');
        }

        if ($pathToCheck === false || ($pathToCheck !== $root && strpos($pathToCheck, $root . DIRECTORY_SEPARATOR) !== 0)) {
            throw new \InvalidArgumentException('Ruta no permitida.');
        }

        return $mustExist ? $pathToCheck : $fullPath;
    }

    protected function getDisplayCloudPath($path, $trailingSlash = true)
    {
        $root = $this->getCloudRoot();
        $path = str_replace('\\', DIRECTORY_SEPARATOR, (string) $path);

        if ($path === $root || strpos($path, $root . DIRECTORY_SEPARATOR) === 0) {
            $path = ltrim(substr($path, strlen($root)), DIRECTORY_SEPARATOR);
        }

        $relativePath = $this->normalizeCloudPath($path);
        $displayPath = './nube/' . ($relativePath == '' ? '' : $relativePath);

        if ($trailingSlash && substr($displayPath, -1) != '/') {
            $displayPath .= '/';
        }

        return $displayPath;
    }

    protected function renderCloudView($id, $path, $prev, $flashKey = null, $flashMessage = null)
    {
        $path = $this->getDisplayCloudPath($path);
        $prev = $prev == './' ? 'NO' : $prev;

        $this->listFolder($path);
        $simple = "'";
        $doble = '"';
        $token = str_replace($doble, $simple, csrf_field());

        if ($flashKey != null && $flashMessage != null) {
            session()->flash($flashKey, $flashMessage);
        }

        return view('web.nube.view')
            ->with('files', $this->rutas)
            ->with('miga_pan', $this->getMigaPan($id))
            ->with('prev', $prev)
            ->with('path', $path)
            ->with('token', $token);
    }


    //index
    public function view()
    {
        return $this->renderCloudView(Input::get('id'), './nube/', 'NO');
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

        try {
            $fullPath = $this->getFullCloudPath($path, true);
        } catch (\Exception $e) {
            return;
        }

        if (is_dir($fullPath)) {
            if ($dh = opendir($fullPath)) {
                $m = 0;
                while (($file = readdir($dh)) !== false) {
                    if ($file == "." || $file == "..") {
                        continue;
                    }

                    $m = $m + 1;
                    $itemPath = $fullPath . DIRECTORY_SEPARATOR . $file;
                    $displayPath = $this->getDisplayCloudPath($itemPath, false);

                    if (is_dir($itemPath)) {
                        $this->rutas[] = [
                            'file' => $file,
                            'path' => $displayPath,
                            'type' => 'FOLDER',
                            'tamanio' => number_format(round(filesize($itemPath) / 1024 / 1024, 4), 2) . ' MB',
                            'extension' => 'FOLDER',
                            'icon' => $this->drawFile('FOLDER'),
                            'color' => $this->drawColor('FOLDER'),
                            'm' => 'FORM_' . $m
                        ];
                    } else {
                        $trozos = explode(".", $file);
                        $extension = end($trozos);
                        $this->rutas[] = [
                            'file' => $file,
                            'path' => $displayPath,
                            'type' => 'FILE',
                            'tamanio' => number_format(round(filesize($itemPath) / 1024 / 1024, 4), 2) . ' MB',
                            'extension' => $extension,
                            'icon' => $this->drawFile($extension),
                            'color' => $this->drawColor($extension),
                            'm' => 'FORM_' . $m
                        ];
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
        try {
            $prev = $this->getPrevious($request->path);
            $path = $prev == './' ? './nube/' : $request->path;

            return $this->renderCloudView($request->id, $path, $prev);
        } catch (\Exception $e) {
            return $this->renderCloudView($request->id, './nube/', 'NO', 'mensaje_error', $e->getMessage());
        }
    }

    //calcula prev
    public function getPrevious($path)
    {
        $path = $this->normalizeCloudPath($path);

        if ($path == '') {
            return './';
        }

        $previous = dirname($path);

        return $previous == '.' ? './' : './nube/' . $previous . '/';
    }

    //delete file
    public function delete(Request $request)
    {
        try {
            $filePath = $this->getFullCloudPath($request->file_id, true);

            if ($request->type == 'FOLDER') {
                $this->rmDir_rf($filePath);
            } else {
                if (!unlink($filePath)) {
                    throw new \RuntimeException('No fue posible eliminar el archivo. Revise permisos de la carpeta nube.');
                }
            }

            return $this->renderCloudView($request->id, $request->path, $request->prev, 'flash_message', 'Elemento eliminado correctamente.');
        } catch (\Exception $e) {
            return $this->renderCloudView($request->id, $request->path, $request->prev, 'mensaje_error', $e->getMessage());
        }
    }

    //Borra directorio
    function rmDir_rf($carpeta)
    {
        foreach (glob($carpeta . DIRECTORY_SEPARATOR . "*") as $archivos_carpeta)
        {
            if (is_dir($archivos_carpeta))
            {
                $this->rmDir_rf($archivos_carpeta);
            } else {
                if ( file_exists( $archivos_carpeta ) )
                    {
                        if (!unlink($archivos_carpeta)) {
                            throw new \RuntimeException('No fue posible eliminar uno de los archivos. Revise permisos de la carpeta nube.');
                        }
                    }
            }
        }

        if (!rmdir($carpeta)) {
            throw new \RuntimeException('No fue posible eliminar la carpeta. Revise permisos de la carpeta nube.');
        }
    }

    //crea nueva carpeta
    public function nueva(Request $request)
    {
        try {
            $name = str_slug($request->name);

            if ($name == '') {
                throw new \InvalidArgumentException('Debe indicar un nombre valido para la carpeta.');
            }

            $folderPath = $this->getFullCloudPath($request->path . '/' . $name);

            if (file_exists($folderPath)) {
                throw new \InvalidArgumentException('Ya existe un archivo o carpeta con ese nombre.');
            }

            if (!mkdir($folderPath, 0775, true)) {
                throw new \RuntimeException('No fue posible crear la carpeta. Revise permisos de la carpeta nube.');
            }

            return $this->renderCloudView($request->id, $request->path, $request->prev, 'flash_message', 'Carpeta creada correctamente.');
        } catch (\Exception $e) {
            return $this->renderCloudView($request->id, $request->path, $request->prev, 'mensaje_error', $e->getMessage());
        }
    }

    //subir archivos
    public function upload(Request $request)
    {
        try {
            if (isset($request->archivo))
            {
                $files = $request->file("archivo");
                foreach ($files as $f) {
                    $name = str_slug(pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME)) . "." . $f->getClientOriginalExtension();
                    $path = $this->getFullCloudPath($request->path . '/' . $name);

                    if (file_put_contents($path, file_get_contents($f->getRealPath()), LOCK_EX) === false) {
                        throw new \RuntimeException('No fue posible subir el archivo. Revise permisos de la carpeta nube.');
                    }

                    chmod($path, 0664);
                }
            }

            return $this->renderCloudView($request->id, $request->path, $request->prev, 'flash_message', 'Archivo(s) subido(s) correctamente.');
        } catch (\Exception $e) {
            return $this->renderCloudView($request->id, $request->path, $request->prev, 'mensaje_error', $e->getMessage());
        }
    }
}
