<?php

namespace App\Calificaciones\Services;

class ImagenesBoletinPdf
{
    private $rutas;
    private $cache = [];

    public function __construct(array $rutas)
    {
        $this->rutas = [];
        foreach ($rutas as $url => $directorio) {
            $this->rutas[$this->normalizarRutaUrl($url)] = $directorio;
        }
    }

    public function prepararHtml($html)
    {
        $html = preg_replace_callback('~(<img\b[^>]*\bsrc\s*=\s*)(["\'])(.*?)\2~is', function ($match) {
            return $match[1] . $match[2] . $this->imagenLocal($match[3]) . $match[2];
        }, $html);

        return preg_replace_callback('~url\(\s*(["\']?)(.*?)\1\s*\)~i', function ($match) {
            return 'url("' . $this->imagenLocal(trim($match[2])) . '")';
        }, $html);
    }


    private function normalizarRutaUrl($url)
    {
        // asset() puede conservar la barra final de url_instancia_cliente.
        // Normalizar sólo la ruta, sin alterar el protocolo, dominio o query.
        return preg_replace_callback('~^((?:https?:)?//[^/?#]+)([^?#]*)~i', function ($match) {
            return $match[1] . preg_replace('~/+~', '/', $match[2]);
        }, $url);
    }

    private function imagenLocal($url)
    {
        if (isset($this->cache[$url])) {
            return $this->cache[$url];
        }

        $this->cache[$url] = $url;
        $urlDecodificada = $this->normalizarRutaUrl(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
        foreach ($this->rutas as $prefijo => $directorio) {
            if (strpos($urlDecodificada, $prefijo) !== 0) {
                continue;
            }

            $relativa = rawurldecode(preg_split('/[?#]/', substr($urlDecodificada, strlen($prefijo)))[0]);
            if (strpos($relativa, "\0") !== false) {
                continue;
            }
            $raiz = realpath($directorio);
            $archivo = $raiz === false ? false : realpath($raiz . DIRECTORY_SEPARATOR . $relativa);
            // Sólo imágenes dentro de los directorios públicos indicados.
            if ($archivo === false || strpos($archivo, $raiz . DIRECTORY_SEPARATOR) !== 0 ||
                !is_file($archivo) || !is_readable($archivo)) {
                continue;
            }
            $imagen = @getimagesize($archivo);
            if ($imagen === false || !in_array($imagen['mime'], ['image/png', 'image/jpeg', 'image/gif'], true)) {
                continue;
            }
            $contenido = file_get_contents($archivo);
            if ($contenido !== false) {
                $this->cache[$url] = 'data:' . $imagen['mime'] . ';base64,' . base64_encode($contenido);
            }
            break;
        }

        return $this->cache[$url];
    }
}
