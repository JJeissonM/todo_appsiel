<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class HotelReservation extends Model
{
    const STATUS_ACTIVA = 'ACTIVA';
    const STATUS_CUMPLIDA = 'CUMPLIDA';
    const STATUS_ANULADA = 'ANULADA';

    protected $table = 'hotel_reservations';

    protected $fillable = array('empresa_id', 'cliente_id', 'room_id', 'reserved_from', 'reserved_until', 'status', 'notes', 'created_by', 'fulfilled_stay_id', 'fulfilled_at');

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Habitacion', 'Cliente', 'Desde', 'Hasta', 'Estado');

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            self::prepareReservation($reservation);
            self::validateAvailability($reservation);
        });

        static::updating(function ($reservation) {
            self::prepareReservation($reservation);
            self::validateAvailability($reservation);
        });
    }

    public static function statuses()
    {
        return array(self::STATUS_ACTIVA, self::STATUS_CUMPLIDA, self::STATUS_ANULADA);
    }

    private static function prepareReservation($reservation)
    {
        if (empty($reservation->empresa_id) && Auth::check()) {
            $reservation->empresa_id = Auth::user()->empresa_id;
        }

        if (empty($reservation->created_by) && Auth::check()) {
            $reservation->created_by = Auth::user()->id;
        }

        if (empty($reservation->status)) {
            $reservation->status = self::STATUS_ACTIVA;
        }

        if (!empty($reservation->reserved_from)) {
            $reservation->reserved_from = substr($reservation->reserved_from, 0, 10);
        }

        if (!empty($reservation->reserved_until)) {
            $reservation->reserved_until = substr($reservation->reserved_until, 0, 10);
        }

        if (empty($reservation->reserved_from) || empty($reservation->reserved_until)) {
            throw new \Exception('Debe ingresar la fecha desde y la fecha hasta de la reserva.');
        }

        if ($reservation->reserved_until < $reservation->reserved_from) {
            throw new \Exception('La fecha hasta de la reserva no puede ser menor que la fecha desde.');
        }
    }

    private static function validateAvailability($reservation)
    {
        if ($reservation->status != self::STATUS_ACTIVA) {
            return;
        }

        $query = self::where('empresa_id', $reservation->empresa_id)
            ->where('room_id', $reservation->room_id)
            ->where('status', self::STATUS_ACTIVA)
            ->where('reserved_from', '<=', $reservation->reserved_until)
            ->where('reserved_until', '>=', $reservation->reserved_from);

        if (!empty($reservation->id)) {
            $query->where('id', '<>', $reservation->id);
        }

        if ($query->count() > 0) {
            throw new \Exception('La habitacion ya tiene una reserva activa en ese rango de fechas.');
        }

        $activeStay = HotelStay::where('empresa_id', $reservation->empresa_id)
            ->where('room_id', $reservation->room_id)
            ->where('status', HotelStay::STATUS_ACTIVA)
            ->where('check_in_at', '<=', $reservation->reserved_until . ' 23:59:59')
            ->where(function ($q) use ($reservation) {
                $q->whereNull('expected_check_out_at')
                    ->orWhere('expected_check_out_at', '>=', $reservation->reserved_from . ' 00:00:00');
            })
            ->count();

        if ($activeStay > 0) {
            throw new \Exception('La habitacion tiene una estadia activa en ese rango de fechas.');
        }
    }

    public function store_adicional($datos, $registro)
    {
        $registro->syncRoomStatus();
        return null;
    }

    public function get_campos_adicionales_create($lista_campos)
    {
        foreach ($lista_campos as $key => $campo) {
            if (!isset($campo['name'])) {
                continue;
            }

            if ($campo['name'] == 'room_id' && Input::get('room_id') != '') {
                $lista_campos[$key]['value'] = Input::get('room_id');
            }

            if ($campo['name'] == 'cliente_id' && Input::get('cliente_id') != '') {
                $lista_campos[$key]['value'] = Input::get('cliente_id');
            }

            if ($campo['name'] == 'reserved_from' && Input::get('reserved_from') != '') {
                $lista_campos[$key]['value'] = Input::get('reserved_from');
            }

            if ($campo['name'] == 'reserved_until' && Input::get('reserved_until') != '') {
                $lista_campos[$key]['value'] = Input::get('reserved_until');
            }
        }

        return $lista_campos;
    }

    public function update_adicional($datos, $id)
    {
        $reservation = self::find($id);
        if (!is_null($reservation)) {
            $reservation->syncRoomStatus();
        }

        return null;
    }

    public function cancel()
    {
        $this->status = self::STATUS_ANULADA;
        $this->save();
        $this->releaseRoomIfNeeded();
    }

    public function fulfill($stayId)
    {
        $this->status = self::STATUS_CUMPLIDA;
        $this->fulfilled_stay_id = $stayId;
        $this->fulfilled_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function syncRoomStatus()
    {
        if ($this->status == self::STATUS_ACTIVA && $this->coversDate(date('Y-m-d'))) {
            $room = $this->room;
            if (!is_null($room) && $room->status == HotelRoom::STATUS_DISPONIBLE) {
                $room->status = HotelRoom::STATUS_RESERVADA;
                $room->save();
            }
        }

        if ($this->status != self::STATUS_ACTIVA) {
            $this->releaseRoomIfNeeded();
        }
    }

    public function releaseRoomIfNeeded()
    {
        $room = $this->room;
        if (is_null($room) || $room->status != HotelRoom::STATUS_RESERVADA) {
            return;
        }

        $hasTodayReservation = self::where('empresa_id', $this->empresa_id)
            ->where('room_id', $this->room_id)
            ->where('status', self::STATUS_ACTIVA)
            ->where('reserved_from', '<=', date('Y-m-d'))
            ->where('reserved_until', '>=', date('Y-m-d'))
            ->count() > 0;

        if (!$hasTodayReservation) {
            $room->status = HotelRoom::STATUS_DISPONIBLE;
            $room->save();
        }
    }

    public function coversDate($date)
    {
        return $this->reserved_from <= $date && $this->reserved_until >= $date;
    }

    public function room()
    {
        return $this->belongsTo('App\Hotel\HotelRoom', 'room_id');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'cliente_id');
    }

    public function stay()
    {
        return $this->belongsTo('App\Hotel\HotelStay', 'fulfilled_stay_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_rooms.room_number AS campo1',
                'core_terceros.descripcion AS campo2',
                'hotel_reservations.reserved_from AS campo3',
                'hotel_reservations.reserved_until AS campo4',
                'hotel_reservations.status AS campo5',
                'hotel_reservations.id AS campo6'
            )
            ->orderBy('hotel_reservations.reserved_from', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_rooms.room_number AS HABITACION',
                'core_terceros.descripcion AS CLIENTE',
                'hotel_reservations.reserved_from AS DESDE',
                'hotel_reservations.reserved_until AS HASTA',
                'hotel_reservations.status AS ESTADO'
            )
            ->toSql();
    }

    public static function tituloExport()
    {
        return 'RESERVAS HOTELERAS';
    }

    public static function opciones_campo_select()
    {
        $query = self::leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_reservations.room_id')
            ->select('hotel_reservations.id', 'hotel_rooms.room_number', 'hotel_reservations.reserved_from', 'hotel_reservations.status')
            ->orderBy('hotel_reservations.id', 'DESC');

        if (Auth::check()) {
            $query->where('hotel_reservations.empresa_id', Auth::user()->empresa_id);
        }

        $options = array('' => '');
        foreach ($query->get() as $reservation) {
            $options[$reservation->id] = '#' . $reservation->id . ' - Hab. ' . $reservation->room_number . ' - ' . $reservation->reserved_from . ' - ' . $reservation->status;
        }

        return $options;
    }

    public function validar_eliminacion($id)
    {
        return 'Las reservas no se eliminan. Use anular.';
    }

    private static function queryForIndex($search)
    {
        $query = self::leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_reservations.room_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'hotel_reservations.cliente_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id');

        if (Auth::check()) {
            $query->where('hotel_reservations.empresa_id', Auth::user()->empresa_id);
        }

        if ($search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('hotel_rooms.room_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('core_terceros.descripcion', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_reservations.status', 'LIKE', '%' . $search . '%');
            });
        }

        return $query;
    }
}
