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
        return self::url('web', array('id_modelo' => self::modelId($namespace)));
    }

    public static function crudCreateUrl($namespace)
    {
        return self::url('web/create', array('id_modelo' => self::modelId($namespace)));
    }

    public static function crudShowUrl($namespace, $recordId)
    {
        return self::url('web/' . $recordId, array('id_modelo' => self::modelId($namespace)));
    }

    public static function url($path, array $overrides = array())
    {
        $params = self::contextParams($overrides);
        if (count($params) == 0) {
            return $path;
        }

        return $path . (strpos($path, '?') === false ? '?' : '&') . http_build_query($params);
    }

    public static function contextQuery(array $overrides = array())
    {
        $params = self::contextParams($overrides);
        return count($params) > 0 ? http_build_query($params) : '';
    }

    public static function contextParams(array $overrides = array())
    {
        $params = array();

        $appId = isset($overrides['id']) ? (int)$overrides['id'] : self::appId();
        if ($appId > 0) {
            $params['id'] = $appId;
        }

        $modelId = isset($overrides['id_modelo']) ? (int)$overrides['id_modelo'] : (int)Input::get('id_modelo');
        if ($modelId > 0) {
            $params['id_modelo'] = $modelId;
        }

        $transactionId = isset($overrides['id_transaccion']) ? (int)$overrides['id_transaccion'] : (int)Input::get('id_transaccion');
        if ($transactionId > 0) {
            $params['id_transaccion'] = $transactionId;
        }

        foreach ($overrides as $key => $value) {
            if (in_array($key, array('id', 'id_modelo', 'id_transaccion'))) {
                continue;
            }

            if (!is_null($value) && $value !== '') {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    public static function ensureContext($modelNamespace = null)
    {
        $params = array();

        if ((int)Input::get('id') <= 0) {
            $params['id'] = self::appId();
        }

        if (!is_null($modelNamespace) && (int)Input::get('id_modelo') <= 0) {
            $modelId = self::modelId($modelNamespace);
            if ($modelId > 0) {
                $params['id_modelo'] = $modelId;
            }
        }

        if (count($params) > 0) {
            request()->merge($params);
        }

        return $params;
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
                'url' => self::url('web', array('id' => self::appIdFromModel($app), 'id_modelo' => $model->id)),
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
