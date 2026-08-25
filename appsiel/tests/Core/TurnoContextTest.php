<?php

use App\Core\Services\TurnoContext;
use App\Core\TurnoOperativo;

class TurnoContextTest extends TestCase
{
    public function test_propaga_el_mismo_turno_y_restaura_el_contexto_anterior()
    {
        $context = new TurnoContext();
        $first = new TurnoOperativo();
        $first->id = 10;
        $second = new TurnoOperativo();
        $second->id = 20;
        $context->set($first);

        $result = $context->run($second, function ($turno) use ($context) {
            $this->assertSame(20, $context->id());
            return $turno->id;
        });

        $this->assertSame(20, $result);
        $this->assertSame(10, $context->id());
    }

    public function test_limpia_el_contexto_aun_si_la_operacion_falla()
    {
        $context = new TurnoContext();
        $turno = new TurnoOperativo();
        $turno->id = 30;

        try {
            $context->run($turno, function () {
                throw new RuntimeException('fallo esperado');
            });
        } catch (RuntimeException $e) {
            $this->assertSame('fallo esperado', $e->getMessage());
        }

        $this->assertNull($context->current());
    }
}
