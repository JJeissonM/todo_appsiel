<?php

namespace App\Core\Services;

use App\Core\Exceptions\TurnoIntegrityException;
use Illuminate\Database\Eloquent\Model;

class TurnoEnvelope
{
    protected $turnoId;
    protected $originType;
    protected $originId;

    public function __construct($turnoId, $originType, $originId)
    {
        $this->turnoId = (int)$turnoId;
        $this->originType = (string)$originType;
        $this->originId = (int)$originId;
    }

    public static function fromOrigin(Model $origin)
    {
        if (!$origin->exists || empty($origin->turno_operativo_id)) {
            throw new TurnoIntegrityException('La operación debe persistirse con turno antes de enviarse a un proceso diferido.');
        }
        return new static($origin->turno_operativo_id, get_class($origin), $origin->getKey());
    }

    public static function fromArray(array $data)
    {
        foreach (array('turno_operativo_id', 'origin_type', 'origin_id') as $field) {
            if (empty($data[$field])) {
                throw new TurnoIntegrityException('El sobre diferido no contiene ' . $field . '.');
            }
        }
        return new static($data['turno_operativo_id'], $data['origin_type'], $data['origin_id']);
    }

    public function toArray()
    {
        return array(
            'turno_operativo_id' => $this->turnoId,
            'origin_type' => $this->originType,
            'origin_id' => $this->originId,
        );
    }

    public function run(\Closure $operation)
    {
        $resolver = app(TurnoAssignmentResolver::class);
        $turno = $resolver->recoverFromOrigin($this->originType, $this->originId);
        if ((int)$turno->id !== $this->turnoId) {
            throw new TurnoIntegrityException('El turno persistido del origen cambió y no coincide con el sobre del proceso diferido.');
        }

        return app(TurnoContext::class)->runFromOrigin($turno, $this->originType, $this->originId, $operation);
    }
}
