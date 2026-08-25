<?php

namespace App\Core\Services;

use App\Core\TurnoOperativo;

class TurnoContext
{
    protected $turno;

    public function set(TurnoOperativo $turno)
    {
        $this->turno = $turno;
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
    }

    public function run(TurnoOperativo $turno, \Closure $operation)
    {
        $previous = $this->turno;
        $this->turno = $turno;

        try {
            return $operation($turno);
        } finally {
            $this->turno = $previous;
        }
    }
}
