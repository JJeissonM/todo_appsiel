<?php

namespace App\Sistema\Services;

use Illuminate\Support\Facades\Storage;

class ImagenService
{
    public function almacenar_imagenes($request, $ruta_storage_imagen, $registro, $modo = null)
    {
        $lista_nombres = '';
        $nombre_es_el_primero = true;
        // Si se envía archivos tipo file (imagenes, adjuntos)
        $archivos_enviados = $request->file();
        foreach ($archivos_enviados as $key => $value) {
            // Si se envía un nuevo archivo, se borran el archivo anterior del disco
            if ($modo == 'edit' && $request->file($key) != '') {
                Storage::delete($ruta_storage_imagen . $registro->$key);
            }

            $archivo = $request->file($key);
            $extension =  $archivo->clientExtension();

            $nuevo_nombre = str_slug($archivo->getClientOriginalName()) . '-' . uniqid() . '.' . $extension;

            // Crear un nombre unico para el archivo con su misma extensión
            //$nuevo_nombre = uniqid() . '.' . $extension;
            if ($nombre_es_el_primero) {
                $lista_nombres .= $nuevo_nombre;
                $nombre_es_el_primero = false;
            } else {
                $lista_nombres .= ',' . $nuevo_nombre;
            }


            // Guardar la imagen en disco
            Storage::put($ruta_storage_imagen . $nuevo_nombre, file_get_contents($archivo->getRealPath()));

            // Guardar nombre en la BD
            $registro->$key = $nuevo_nombre;
            $registro->save();
        }

        return $lista_nombres;
    }
}
