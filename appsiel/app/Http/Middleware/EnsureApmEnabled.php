<?php

namespace App\Http\Middleware;

use App\Sistema\Modelo;
use App\Ventas\Services\ApmAvailability;
use Closure;

class EnsureApmEnabled
{
    public function handle($request, Closure $next)
    {
        if (!ApmAvailability::enabled()) {
            if ($request->is('apm_*', 'apm_*/*', 'tesoreria_pagos_cxp_apm_payload/*')) {
                abort(404);
            }

            $modelId = $request->input('id_modelo', $request->input('url_id_modelo',
                $request->route('modelo_id') ?: $request->route('model_id')));
            if ($modelId) {
                $model = Modelo::find($modelId);
                if ($model && ApmAvailability::isApmModel($model->name_space)) {
                    abort(404);
                }
            }
        }

        return $next($request);
    }
}
