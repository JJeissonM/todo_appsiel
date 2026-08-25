<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class TurnoConfiguracion extends Model
{
    const MODO_TRADICIONAL = 'TRADICIONAL';
    const MODO_TURNOS = 'TURNOS';

    protected $table = 'core_turno_configuraciones';

    protected $fillable = array(
        'core_empresa_id', 'modulo', 'contexto_tipo', 'contexto_id', 'modo',
        'creado_por', 'modificado_por'
    );

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($configuration) {
            $analysis = app(\App\Core\Services\TurnoConfigurationService::class)->analyzeCandidate($configuration->getAttributes());
            $errors = $analysis['errors'];
            if (!empty($errors)) {
                throw new \App\Core\Exceptions\TurnoIntegrityException(implode(' ', $errors));
            }
        });
        static::saved(function () {
            app(\App\Core\Services\TurnoModeResolver::class)->clearCache();
        });
    }
}
