<?php

namespace App\Hotel\Services;

use App\Hotel\HotelReservation;
use App\Hotel\HotelRoom;
use App\Hotel\HotelStay;

class HotelReservationService
{
    /**
     * Normaliza los valores recibidos por los controles datetime-local.
     * Los valores históricos que solo contienen fecha conservan la semántica
     * anterior: inicio del día para "desde" y final del día para "hasta".
     */
    public function normalizeDateTime($value, $endOfDayForDateOnly = false)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        $value = trim(str_replace('T', ' ', (string)$value));
        if ($value === '') {
            return null;
        }

        $formats = array('Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d');
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            $errors = \DateTime::getLastErrors();
            $valid = $date !== false
                && ($errors === false || ($errors['warning_count'] == 0 && $errors['error_count'] == 0))
                && $date->format($format) === $value;

            if (!$valid) {
                continue;
            }

            if ($format === 'Y-m-d' && $endOfDayForDateOnly) {
                // El final es exclusivo. El día completo termina exactamente al
                // comenzar el día siguiente, sin dejar un segundo sin cubrir.
                $date->modify('+1 day');
            }

            return $date->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function prepare($reservation)
    {
        $reservation->reserved_from = $this->normalizeDateTime($reservation->reserved_from, false);
        $reservation->reserved_until = $this->normalizeDateTime($reservation->reserved_until, true);

        return $reservation;
    }

    public function getPreparationError($reservation)
    {
        if (empty($reservation->reserved_from) || empty($reservation->reserved_until)) {
            return 'Debe ingresar fechas y horas validas para el inicio y la finalizacion de la reserva.';
        }

        if ($reservation->reserved_until <= $reservation->reserved_from) {
            return 'La fecha y hora de finalizacion debe ser posterior a la fecha y hora de inicio de la reserva.';
        }

        return null;
    }

    public function getAvailabilityError($reservation)
    {
        if ($reservation->status != HotelReservation::STATUS_ACTIVA) {
            return null;
        }

        if (empty($reservation->room_id) || empty($reservation->reserved_from) || empty($reservation->reserved_until)) {
            return null;
        }

        $room = HotelRoom::where('empresa_id', $reservation->empresa_id)
            ->where('id', $reservation->room_id)
            ->first();

        if (is_null($room)) {
            return 'La habitacion seleccionada no existe.';
        }

        if ((int)$room->is_active != 1) {
            return 'La habitacion seleccionada esta inactiva y no puede reservarse.';
        }

        if ($room->status == HotelRoom::STATUS_BLOQUEADA) {
            return 'La habitacion seleccionada esta bloqueada y no puede reservarse.';
        }

        // Los intervalos se manejan como [inicio, fin). Si una reserva termina
        // exactamente cuando comienza la siguiente, no existe solapamiento.
        $query = HotelReservation::where('empresa_id', $reservation->empresa_id)
            ->where('room_id', $reservation->room_id)
            ->whereNotIn('status', array(HotelReservation::STATUS_ANULADA, HotelReservation::STATUS_CUMPLIDA))
            ->where('reserved_from', '<', $reservation->reserved_until)
            ->where('reserved_until', '>', $reservation->reserved_from);

        if (!empty($reservation->id)) {
            $query->where('id', '<>', $reservation->id);
        }

        if ($query->exists()) {
            return 'La habitacion ya tiene una reserva activa que se cruza con ese rango de fecha y hora.';
        }

        $activeStay = HotelStay::where('empresa_id', $reservation->empresa_id)
            ->where('room_id', $reservation->room_id)
            ->where('status', HotelStay::STATUS_ACTIVA)
            ->where('check_in_at', '<', $reservation->reserved_until)
            ->where(function ($query) use ($reservation) {
                $query->whereNull('expected_check_out_at')
                    ->orWhere('expected_check_out_at', '>', $reservation->reserved_from);
            })
            ->exists();

        if ($activeStay) {
            return 'La habitacion tiene una estadia activa que se cruza con ese rango de fecha y hora.';
        }

        return null;
    }

    public function intervalsOverlap($firstStart, $firstEnd, $secondStart, $secondEnd)
    {
        return $firstStart < $secondEnd && $firstEnd > $secondStart;
    }

    public function coversMoment($reservation, $moment)
    {
        $moment = $this->normalizeDateTime($moment, false);

        return !is_null($moment)
            && $reservation->reserved_from <= $moment
            && $reservation->reserved_until > $moment;
    }
}
