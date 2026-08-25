<?php

namespace App\Core\Services;

use App\Core\TurnoEvento;
use App\Core\TurnoOperativo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TurnoManager
{
    protected $modeResolver;
    protected $context;

    public function __construct(TurnoModeResolver $modeResolver, TurnoContext $context)
    {
        $this->modeResolver = $modeResolver;
        $this->context = $context;
    }

    public function enabledForPdv($empresaId, $pdvId, $module = 'ventas_pos')
    {
        return $this->modeResolver->enabled($empresaId, $module, 'pdv', $pdvId);
    }

    public function currentForPdv($empresaId, $pdvId, $lock = false)
    {
        $query = TurnoOperativo::where('core_empresa_id', (int)$empresaId)
            ->where('contexto_tipo', 'pdv')
            ->where('contexto_id', (int)$pdvId)
            ->abiertos()
            ->orderBy('id', 'DESC');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function assertCanOpen($empresaId, $pdvId)
    {
        if ($this->enabledForPdv($empresaId, $pdvId) && !is_null($this->currentForPdv($empresaId, $pdvId))) {
            throw new \UnexpectedValueException('El punto de venta ya tiene un turno operativo abierto. Debe cerrarlo antes de iniciar otro.');
        }
    }

    public function assertCanClose($empresaId, $pdvId)
    {
        if ($this->enabledForPdv($empresaId, $pdvId) && is_null($this->currentForPdv($empresaId, $pdvId))) {
            throw new \UnexpectedValueException('El punto de venta no tiene un turno operativo abierto para cerrar.');
        }
    }

    public function openFromLegacy(Model $opening, $userId = null)
    {
        if (!$this->enabledForPdv($opening->core_empresa_id, $opening->pdv_id)) {
            return null;
        }

        $manager = $this;
        return DB::transaction(function () use ($opening, $userId, $manager) {
            $existing = $manager->currentForPdv($opening->core_empresa_id, $opening->pdv_id, true);
            if (!is_null($existing)) {
                throw new \UnexpectedValueException('Ya existe un turno operativo abierto para este punto de venta.');
            }

            $openedAt = $opening->created_at ?: date('Y-m-d H:i:s');
            $contextKey = 'pdv:' . (int)$opening->core_empresa_id . ':' . (int)$opening->pdv_id;
            $pdv = \App\VentasPos\Pdv::find($opening->pdv_id);
            $turno = TurnoOperativo::create(array(
                'core_empresa_id' => $opening->core_empresa_id,
                'contexto_tipo' => 'pdv',
                'contexto_id' => $opening->pdv_id,
                'pdv_id' => $opening->pdv_id,
                'teso_caja_id' => is_null($pdv) ? null : $pdv->caja_default_id,
                'fecha_operativa' => substr((string)$opening->fecha, 0, 10),
                'abierto_en' => $openedAt,
                'abierto_por' => $userId ?: $opening->cajero_id,
                'saldo_inicial' => $opening->efectivo_base ?: 0,
                'estado' => TurnoOperativo::ESTADO_ABIERTO,
                'codigo' => 'TUR-' . $opening->core_empresa_id . '-' . $opening->pdv_id . '-' . date('YmdHis', strtotime($openedAt)) . '-' . $opening->id,
                'clave_contexto_abierto' => $contextKey,
            ));

            $opening->turno_operativo_id = $turno->id;
            $opening->save();
            $manager->recordEvent($turno, 'APERTURA', null, TurnoOperativo::ESTADO_ABIERTO, $opening, $userId, null);
            $manager->context->set($turno);

            return $turno;
        });
    }

    public function closeFromLegacy(Model $closing, $userId = null, $closingBalance = null)
    {
        if (!$this->enabledForPdv($closing->core_empresa_id, $closing->pdv_id)) {
            return null;
        }

        $manager = $this;
        return DB::transaction(function () use ($closing, $userId, $closingBalance, $manager) {
            $turno = $manager->currentForPdv($closing->core_empresa_id, $closing->pdv_id, true);
            if (is_null($turno)) {
                throw new \UnexpectedValueException('No existe un turno operativo abierto para este punto de venta.');
            }

            $previous = $turno->estado;
            $turno->cerrado_en = $closing->created_at ?: date('Y-m-d H:i:s');
            $turno->cerrado_por = $userId ?: $closing->cajero_id;
            $turno->saldo_cierre = $closingBalance;
            $turno->estado = TurnoOperativo::ESTADO_CERRADO;
            $turno->clave_contexto_abierto = null;
            $turno->save();

            $closing->turno_operativo_id = $turno->id;
            // La fecha del cierre historico se conserva; la fecha operativa vive en el turno.
            $closing->save();
            $manager->recordEvent($turno, 'CIERRE', $previous, $turno->estado, $closing, $userId, null);
            $manager->context->clear();

            return $turno;
        });
    }

    public function assignAdjustment(Model $movement, TurnoOperativo $turno, $reason, $userId = null)
    {
        if (trim((string)$reason) === '') {
            throw new \InvalidArgumentException('Los ajustes posteriores deben indicar un motivo de auditoria.');
        }
        $movementCompany = $movement->getAttribute('core_empresa_id') ?: $movement->getAttribute('empresa_id');
        if ((int)$movementCompany !== (int)$turno->core_empresa_id) {
            throw new \UnexpectedValueException('El movimiento y el turno deben pertenecer a la misma empresa.');
        }

        $movement->setAttribute('turno_operativo_id', $turno->id);
        $movement->save();
        $this->recordEvent($turno, 'AJUSTE_POSTERIOR', $turno->estado, $turno->estado, $movement, $userId, $reason);
        return $movement;
    }

    public function reopen(TurnoOperativo $turno, $reason, $userId = null)
    {
        if (trim((string)$reason) === '') {
            throw new \InvalidArgumentException('La reapertura requiere un motivo de auditoria.');
        }

        $manager = $this;
        return DB::transaction(function () use ($turno, $reason, $userId, $manager) {
            $turno = TurnoOperativo::where('id', $turno->id)->lockForUpdate()->firstOrFail();
            if (!is_null($manager->currentForPdv($turno->core_empresa_id, $turno->contexto_id, true))) {
                throw new \UnexpectedValueException('Ya existe otro turno abierto para este contexto.');
            }
            $previous = $turno->estado;
            $turno->estado = TurnoOperativo::ESTADO_ABIERTO;
            $turno->cerrado_en = null;
            $turno->cerrado_por = null;
            $turno->clave_contexto_abierto = 'pdv:' . $turno->core_empresa_id . ':' . $turno->contexto_id;
            $turno->save();
            $manager->recordEvent($turno, 'REAPERTURA', $previous, $turno->estado, null, $userId, $reason);
            return $turno;
        });
    }

    public function startAudit(TurnoOperativo $turno, $userId = null, $reason = null)
    {
        if ($turno->estado !== TurnoOperativo::ESTADO_CERRADO) {
            throw new \UnexpectedValueException('Solo un turno cerrado puede pasar a auditoria.');
        }
        return $this->transition($turno, TurnoOperativo::ESTADO_AUDITANDO, 'INICIO_AUDITORIA', $userId, $reason);
    }

    public function completeAudit(TurnoOperativo $turno, $userId = null, $reason = null)
    {
        if ($turno->estado !== TurnoOperativo::ESTADO_AUDITANDO) {
            throw new \UnexpectedValueException('El turno debe estar en auditoria antes de marcarse auditado.');
        }
        return $this->transition($turno, TurnoOperativo::ESTADO_AUDITADO, 'FIN_AUDITORIA', $userId, $reason);
    }

    protected function transition(TurnoOperativo $turno, $newState, $event, $userId, $reason)
    {
        $previous = $turno->estado;
        $turno->estado = $newState;
        $turno->save();
        $this->recordEvent($turno, $event, $previous, $newState, null, $userId, $reason);
        return $turno;
    }

    public function recordEvent(TurnoOperativo $turno, $type, $previous, $new, Model $entity = null, $userId = null, $reason = null, array $data = array())
    {
        return TurnoEvento::create(array(
            'turno_operativo_id' => $turno->id,
            'tipo' => $type,
            'estado_anterior' => $previous,
            'estado_nuevo' => $new,
            'entidad_tipo' => is_null($entity) ? null : get_class($entity),
            'entidad_id' => is_null($entity) ? null : $entity->getKey(),
            'usuario_id' => $userId,
            'motivo' => $reason,
            'datos' => empty($data) ? null : json_encode($data),
        ));
    }
}
