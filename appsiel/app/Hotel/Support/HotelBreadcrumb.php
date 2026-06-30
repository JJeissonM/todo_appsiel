<?php

namespace App\Hotel\Support;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use Illuminate\Support\Facades\Input;

class HotelBreadcrumb
{
    public static function make($modelNamespace, $label)
    {
        $app = self::application();
        $model = self::model($modelNamespace);

        $breadcrumb = array(
            array('url' => self::appUrl($app), 'etiqueta' => self::appLabel($app)),
        );

        if (!is_null($model)) {
            $breadcrumb[] = array(
                'url' => 'web?id=' . self::appId($app) . '&id_modelo=' . $model->id,
                'etiqueta' => $model->descripcion,
            );
        }

        $breadcrumb[] = array('url' => 'NO', 'etiqueta' => $label);

        return $breadcrumb;
    }

    public static function dashboard($label)
    {
        $app = self::application();

        return array(
            array('url' => self::appUrl($app), 'etiqueta' => self::appLabel($app)),
            array('url' => 'NO', 'etiqueta' => $label),
        );
    }

    private static function application()
    {
        $appId = (int)Input::get('id');
        if ($appId > 0) {
            $app = Aplicacion::find($appId);
            if (!is_null($app)) {
                return $app;
            }
        }

        return Aplicacion::where('app', 'hotel')->first();
    }

    private static function model($namespace)
    {
        return Modelo::where('name_space', $namespace)->first();
    }

    private static function appUrl($app)
    {
        $appPath = !is_null($app) && $app->app != '' ? $app->app : 'hotel';
        return $appPath . '?id=' . self::appId($app);
    }

    private static function appLabel($app)
    {
        return !is_null($app) && $app->descripcion != '' ? $app->descripcion : 'Gestion Hotelera';
    }

    private static function appId($app)
    {
        if (!is_null($app) && (int)$app->id > 0) {
            return (int)$app->id;
        }

        return (int)Input::get('id');
    }
}
