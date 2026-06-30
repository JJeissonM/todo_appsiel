<?php

namespace App\Hotel\Support;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use Illuminate\Support\Facades\Input;

class HotelBreadcrumb
{
    public static function appId()
    {
        return self::appIdFromModel(self::application());
    }

    public static function modelId($namespace)
    {
        $model = self::model($namespace);
        return !is_null($model) ? (int)$model->id : 0;
    }

    public static function crudIndexUrl($namespace)
    {
        return 'web?id=' . self::appId() . '&id_modelo=' . self::modelId($namespace);
    }

    public static function crudCreateUrl($namespace)
    {
        return 'web/create?id=' . self::appId() . '&id_modelo=' . self::modelId($namespace);
    }

    public static function crudShowUrl($namespace, $recordId)
    {
        return 'web/' . $recordId . '?id=' . self::appId() . '&id_modelo=' . self::modelId($namespace);
    }

    public static function make($modelNamespace, $label)
    {
        $app = self::application();
        $model = self::model($modelNamespace);

        $breadcrumb = array(
            array('url' => self::appUrl($app), 'etiqueta' => self::appLabel($app)),
        );

        if (!is_null($model)) {
            $breadcrumb[] = array(
                'url' => 'web?id=' . self::appIdFromModel($app) . '&id_modelo=' . $model->id,
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
        return $appPath . '?id=' . self::appIdFromModel($app);
    }

    private static function appLabel($app)
    {
        return !is_null($app) && $app->descripcion != '' ? $app->descripcion : 'Gestion Hotelera';
    }

    private static function appIdFromModel($app)
    {
        if (!is_null($app) && (int)$app->id > 0) {
            return (int)$app->id;
        }

        return (int)Input::get('id');
    }
}
