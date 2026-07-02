<?php

namespace App\Hotel;

use App\Hotel\Services\HotelService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class HotelStay extends Model
{
    const STATUS_ACTIVA = 'ACTIVA';
    const STATUS_CERRADA = 'CERRADA';
    const STATUS_ANULADA = 'ANULADA';

    protected $table = 'hotel_stays';

    protected $fillable = array('empresa_id', 'main_cliente_id', 'room_id', 'check_in_at', 'expected_check_out_at', 'check_out_at', 'adults_count', 'children_count', 'total_guests', 'status', 'notes', 'created_by', 'closed_by');

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Habitacion', 'Cliente principal', 'Check-in', 'Salida esperada', 'Check-out', 'Huespedes', 'Estado');

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"hotel/stays/id_fila"}';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stay) {
            if (empty($stay->empresa_id) && Auth::check()) {
                $stay->empresa_id = Auth::user()->empresa_id;
            }

            if (empty($stay->created_by) && Auth::check()) {
                $stay->created_by = Auth::user()->id;
            }

            if (empty($stay->status) || !in_array($stay->status, self::statuses())) {
                $stay->status = self::STATUS_ACTIVA;
            }

            if (empty($stay->check_in_at)) {
                $stay->check_in_at = date('Y-m-d H:i:s');
            }

            $stay->total_guests = max(1, (int)$stay->adults_count + (int)$stay->children_count);
            self::validateCheckInAvailability($stay);
        });

        static::updating(function ($stay) {
            $stay->total_guests = max(1, (int)$stay->adults_count + (int)$stay->children_count);
        });
    }

    public static function statuses()
    {
        return array(self::STATUS_ACTIVA, self::STATUS_CERRADA, self::STATUS_ANULADA);
    }

    public function store_adicional($datos, $registro)
    {
        $registro->ensureCheckInRecords();

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

            if ($campo['name'] == 'main_cliente_id' && Input::get('main_cliente_id') != '') {
                $lista_campos[$key]['value'] = Input::get('main_cliente_id');
            }
        }

        return $lista_campos;
    }

    public function ensureCheckInRecords()
    {
        if ($this->status != self::STATUS_ACTIVA && !in_array($this->status, self::statuses())) {
            $this->status = self::STATUS_ACTIVA;
            $this->save();
        }

        $stayId = $this->id;

        DB::transaction(function () use ($stayId) {
            $stay = self::where('id', $stayId)->lockForUpdate()->first();
            if (is_null($stay)) {
                return;
            }

            if ($stay->status != self::STATUS_ACTIVA && !in_array($stay->status, self::statuses())) {
                $stay->status = self::STATUS_ACTIVA;
                $stay->save();
            }

            $mainGuest = HotelStayGuest::firstOrCreate(array(
                'empresa_id' => $stay->empresa_id,
                'stay_id' => $stay->id,
                'cliente_id' => $stay->main_cliente_id,
            ), array(
                'is_main_guest' => 1,
            ));

            if ((int)$mainGuest->is_main_guest != 1) {
                $mainGuest->is_main_guest = 1;
                $mainGuest->save();
            }

            $room = HotelRoom::where('empresa_id', $stay->empresa_id)->where('id', $stay->room_id)->first();
            $reservation = self::reservationForCheckIn($stay);
            if (!is_null($room) && $stay->status == self::STATUS_ACTIVA && in_array($room->status, array(HotelRoom::STATUS_DISPONIBLE, HotelRoom::STATUS_RESERVADA))) {
                $room->status = HotelRoom::STATUS_OCUPADA;
                $room->save();
            }

            if (!is_null($reservation)) {
                $reservation->fulfill($stay->id);
            }

            $ordersCount = HotelOrderHeader::where('empresa_id', $stay->empresa_id)->where('stay_id', $stay->id)->count();
            if ($ordersCount == 0) {
                (new HotelService())->createOrderForStay($stay, true);
            }
        });
    }

    private static function validateCheckInAvailability($stay)
    {
        if ($stay->status != self::STATUS_ACTIVA) {
            return;
        }

        $activeStay = self::where('empresa_id', $stay->empresa_id)
            ->where('room_id', $stay->room_id)
            ->where('status', self::STATUS_ACTIVA)
            ->count();

        if ($activeStay > 0) {
            throw new \Exception('La habitacion ya tiene una estadia activa.');
        }

        $room = HotelRoom::where('empresa_id', $stay->empresa_id)->where('id', $stay->room_id)->first();
        if (is_null($room) || (int)$room->is_active != 1 || (int)$room->inv_producto_id <= 0) {
            throw new \Exception('La habitacion no esta disponible para check-in.');
        }

        if ($room->status == HotelRoom::STATUS_DISPONIBLE) {
            return;
        }

        if ($room->status == HotelRoom::STATUS_RESERVADA && !is_null(self::reservationForCheckIn($stay))) {
            return;
        }

        throw new \Exception('La habitacion no esta disponible para check-in.');
    }

    private static function reservationForCheckIn($stay)
    {
        $date = substr($stay->check_in_at, 0, 10);

        return HotelReservation::where('empresa_id', $stay->empresa_id)
            ->where('room_id', $stay->room_id)
            ->where('cliente_id', $stay->main_cliente_id)
            ->where('status', HotelReservation::STATUS_ACTIVA)
            ->where('reserved_from', '<=', $date)
            ->where('reserved_until', '>=', $date)
            ->first();
    }

    public function setCheckInAtAttribute($value)
    {
        $this->attributes['check_in_at'] = self::normalizeDateTimeValue($value);
    }

    public function setExpectedCheckOutAtAttribute($value)
    {
        $this->attributes['expected_check_out_at'] = self::normalizeDateTimeValue($value);
    }

    public function setCheckOutAtAttribute($value)
    {
        $this->attributes['check_out_at'] = self::normalizeDateTimeValue($value);
    }

    public function room()
    {
        return $this->belongsTo('App\Hotel\HotelRoom', 'room_id');
    }

    public function mainGuest()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'main_cliente_id');
    }

    public function guests()
    {
        return $this->hasMany('App\Hotel\HotelStayGuest', 'stay_id');
    }

    public function order()
    {
        return $this->hasOne('App\Hotel\HotelOrderHeader', 'stay_id')->orderBy('id', 'DESC');
    }

    public function orders()
    {
        return $this->hasMany('App\Hotel\HotelOrderHeader', 'stay_id')->orderBy('id', 'DESC');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_rooms.room_number AS campo1',
                'core_terceros.descripcion AS campo2',
                'hotel_stays.check_in_at AS campo3',
                'hotel_stays.expected_check_out_at AS campo4',
                'hotel_stays.check_out_at AS campo5',
                'hotel_stays.total_guests AS campo6',
                'hotel_stays.status AS campo7',
                'hotel_stays.id AS campo8'
            )
            ->orderBy('hotel_stays.check_in_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_rooms.room_number AS HABITACION',
                'core_terceros.descripcion AS CLIENTE',
                'hotel_stays.check_in_at AS CHECK_IN',
                'hotel_stays.expected_check_out_at AS SALIDA_ESPERADA',
                'hotel_stays.check_out_at AS CHECK_OUT',
                'hotel_stays.total_guests AS HUESPEDES',
                'hotel_stays.status AS ESTADO'
            )
            ->toSql();
    }

    public static function tituloExport()
    {
        return 'ESTADIAS HOTELERAS';
    }

    public static function opciones_campo_select()
    {
        $query = self::leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_stays.room_id')
            ->select('hotel_stays.id', 'hotel_rooms.room_number', 'hotel_stays.status')
            ->orderBy('hotel_stays.id', 'DESC');

        if (Auth::check()) {
            $query->where('hotel_stays.empresa_id', Auth::user()->empresa_id);
        }

        $options = array('' => '');
        foreach ($query->get() as $stay) {
            $options[$stay->id] = '#' . $stay->id . ' - Hab. ' . $stay->room_number . ' - ' . $stay->status;
        }

        return $options;
    }

    public function validar_eliminacion($id)
    {
        return 'Las estadias no se eliminan. Use estado ANULADA.';
    }

    private static function queryForIndex($search)
    {
        $query = self::leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_stays.room_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'hotel_stays.main_cliente_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id');

        if (Auth::check()) {
            $query->where('hotel_stays.empresa_id', Auth::user()->empresa_id);
        }

        if ($search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('hotel_rooms.room_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('core_terceros.descripcion', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_stays.status', 'LIKE', '%' . $search . '%');
            });
        }

        return $query;
    }

    private static function normalizeDateTimeValue($value)
    {
        if (is_null($value) || $value === '' || $value === 'null') {
            return null;
        }

        $value = trim((string)$value);
        $value = str_replace('T', ' ', $value);

        if (strlen($value) == 16) {
            return $value . ':00';
        }

        return $value;
    }
}
