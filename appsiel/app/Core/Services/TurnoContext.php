<?php

namespace App\Core\Services;

use App\Core\TurnoOperativo;

class TurnoContext
{
    protected $turno;
    protected $origin;
    protected $allowHistorical = false;

    public function set(TurnoOperativo $turno)
    {
        $this->turno = $turno;
        $this->origin = null;
        $this->allowHistorical = false;
        return $this;
    }

    public function setFromOrigin(TurnoOperativo $turno, $originType, $originId)
    {
        $this->turno = $turno;
        $this->origin = array('type' => $originType, 'id' => (int)$originId);
        $this->allowHistorical = true;
        return $this;
    }

    public function current()
    {
        return $this->turno;
    }

    public function id()
    {
        return is_null($this->turno) ? null : (int)$this->turno->id;
    }

    public function clear()
    {
        $this->turno = null;
        $this->origin = null;
        $this->allowHistorical = false;
    }

    public function allowsHistoricalTurn()
    {
        return $this->allowHistorical;
    }

    public function origin()
    {
        return $this->origin;
    }

    public function run(TurnoOperativo $turno, \Closure $operation)
    {
        $previous = $this->turno;
        $previousOrigin = $this->origin;
        $previousHistorical = $this->allowHistorical;
        $this->turno = $turno;
        $this->origin = null;
        $this->allowHistorical = false;

        try {
            return $operation($turno);
        } finally {
            $this->turno = $previous;
            $this->origin = $previousOrigin;
            $this->allowHistorical = $previousHistorical;
        }
    }

    public function runFromOrigin(TurnoOperativo $turno, $originType, $originId, \Closure $operation)
    {
        $previous = $this->turno;
        $previousOrigin = $this->origin;
        $previousHistorical = $this->allowHistorical;
        $this->setFromOrigin($turno, $originType, $originId);

        try {
            return $operation($turno);
        } finally {
            $this->turno = $previous;
            $this->origin = $previousOrigin;
            $this->allowHistorical = $previousHistorical;
        }
    }
}
