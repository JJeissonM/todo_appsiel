<?php

use App\Hotel\Services\HotelReservationService;

class HotelReservationServiceTest extends TestCase
{
    public function test_permite_una_reserva_que_inicia_exactamente_al_finalizar_otra()
    {
        $service = new HotelReservationService();

        $this->assertFalse($service->intervalsOverlap(
            '2030-01-10 08:00:00',
            '2030-01-10 11:00:00',
            '2030-01-10 11:00:00',
            '2030-01-10 15:00:00'
        ));
    }

    public function test_detecta_solapamiento_aunque_sea_de_un_segundo()
    {
        $service = new HotelReservationService();

        $this->assertTrue($service->intervalsOverlap(
            '2030-01-10 08:00:00',
            '2030-01-10 11:00:01',
            '2030-01-10 11:00:00',
            '2030-01-10 15:00:00'
        ));
    }

    public function test_normaliza_datetime_local_y_conserva_fechas_historicas_como_dias_completos()
    {
        $service = new HotelReservationService();

        $this->assertSame('2030-01-10 08:30:00', $service->normalizeDateTime('2030-01-10T08:30'));
        $this->assertSame('2030-01-10 00:00:00', $service->normalizeDateTime('2030-01-10'));
        $this->assertSame('2030-01-11 00:00:00', $service->normalizeDateTime('2030-01-10', true));
    }

    public function test_rechaza_reservas_sin_duracion()
    {
        $service = new HotelReservationService();
        $reservation = (object)array(
            'reserved_from' => '2030-01-10 11:00:00',
            'reserved_until' => '2030-01-10 11:00:00',
        );

        $this->assertNotNull($service->getPreparationError($reservation));
    }
}
