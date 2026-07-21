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

    protected $fillable = array('empresa_id', 'main_cliente_id', 'room_id', 'check_in_at', 'expected_check_out_at', 'check_out_at', 'adults_count', 'children_count', 'total_guests', 'status', 'notes', 'created_by', 'closed_by', 'update_by');

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Habitacion', 'Cliente principal', 'Check-in', 'Salida esperada', 'Dias', 'Huespedes', 'Estado');

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
            $message = self::getStayDatesError($stay);
            if (!is_null($message)) {
                throw new \Exception($message);
            }
            self::validateCheckInAvailability($stay);
        });

        static::updating(function ($stay) {
            if (Auth::check()) {
                $stay->update_by = Auth::user()->id;
            }

            $stay->total_guests = max(1, (int)$stay->adults_count + (int)$stay->children_count);

            $message = self::getStayDatesError($stay);
            if (!is_null($message)) {
                throw new \Exception($message);
            }

            self::validateInvoicedRoomLineBeforeDateEdit($stay);
            self::syncPrimaryOrderRoomQuantityOnDateChange($stay);
        });
    }

    public static function statuses()
    {
        return array(self::STATUS_ACTIVA, self::STATUS_CERRADA, self::STATUS_ANULADA);
    }

    public function creador_por()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function modificador_por()
    {
        return $this->belongsTo('App\User', 'update_by');
    }

    public function validar_datos_creacion($request, $controller)
    {
        $controller->validate($request, array(
            'main_cliente_id' => 'required',
            'room_id' => 'required',
            'expected_check_out_at' => 'required',
        ), array(
            'main_cliente_id.required' => 'Debe seleccionar el huesped principal.',
            'room_id.required' => 'Debe seleccionar una habitacion.',
            'expected_check_out_at.required' => 'Debe ingresar la salida esperada.',
        ));

        $validator = \Validator::make($request->all(), array());
        $validator->after(function ($validator) use ($request) {
            $stay = new self;
            $stay->empresa_id = $request->empresa_id;
            if (empty($stay->empresa_id) && Auth::check()) {
                $stay->empresa_id = Auth::user()->empresa_id;
            }

            $stay->main_cliente_id = $request->main_cliente_id;
            $stay->room_id = $request->room_id;
            $stay->check_in_at = !empty($request->check_in_at) ? self::normalizeDateTimeValue($request->check_in_at) : date('Y-m-d H:i:s');
            $stay->expected_check_out_at = self::normalizeDateTimeValue($request->expected_check_out_at);
            $stay->status = in_array($request->status, self::statuses()) ? $request->status : self::STATUS_ACTIVA;

            $message = self::getStayDatesError($stay);
            if (is_null($message)) {
                $message = self::getCheckInAvailabilityError($stay);
            }

            if (!is_null($message)) {
                $validator->errors()->add('room_id', $message);
            }
        });

        $controller->validateWith($validator, $request);
    }

    public function store_adicional($datos, $registro)
    {
        $registro->ensureCheckInRecords();

        return null;
    }

    public function get_campos_adicionales_create($lista_campos)
    {
        $lista_campos = $this->setCustomerAutocompleteField($lista_campos);

        foreach ($lista_campos as $key => $campo) {
            if (!isset($campo['name'])) {
                continue;
            }

            if ($campo['name'] == 'room_id' && Input::get('room_id') != '') {
                $lista_campos[$key]['value'] = Input::get('room_id');
            }

            if ($campo['name'] == 'room_id') {
                $lista_campos[$key]['requerido'] = 1;
                if (!isset($lista_campos[$key]['atributos']) || !is_array($lista_campos[$key]['atributos'])) {
                    $lista_campos[$key]['atributos'] = array();
                }
                $lista_campos[$key]['atributos']['required'] = 'required';
            }

            if ($campo['name'] == 'expected_check_out_at') {
                $lista_campos[$key]['requerido'] = 1;
                if (!isset($lista_campos[$key]['atributos']) || !is_array($lista_campos[$key]['atributos'])) {
                    $lista_campos[$key]['atributos'] = array();
                }
                $lista_campos[$key]['atributos']['required'] = 'required';
            }

            if ($campo['name'] == 'check_out_at') {
                unset($lista_campos[$key]);
            }

            if ($campo['name'] == 'main_cliente_id' && Input::get('main_cliente_id') != '') {
                $lista_campos[$key]['value'] = Input::get('main_cliente_id');
            }
        }

        return array_values($lista_campos);
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $lista_campos = $this->setCustomerAutocompleteField($lista_campos);

        foreach ($lista_campos as $key => $campo) {
            if (isset($campo['name']) && $campo['name'] == 'check_out_at') {
                unset($lista_campos[$key]);
            }
        }

        return array_values($lista_campos);
    }

    private function setCustomerAutocompleteField($lista_campos)
    {
        foreach ($lista_campos as $key => $campo) {
            if (isset($campo['name']) && $campo['name'] == 'main_cliente_id') {
                $lista_campos[$key]['tipo'] = 'cliente_autocomplete';
                $lista_campos[$key]['opciones'] = '';
                $lista_campos[$key]['atributos'] = array('class' => 'form-control');
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
        $message = self::getCheckInAvailabilityError($stay);

        if (!is_null($message)) {
            throw new \Exception($message);
        }
    }

    private static function getCheckInAvailabilityError($stay)
    {
        if ($stay->status != self::STATUS_ACTIVA) {
            return null;
        }

        if (empty($stay->room_id)) {
            return 'Debe seleccionar una habitacion para el check-in.';
        }

        $activeStay = self::where('empresa_id', $stay->empresa_id)
            ->where('room_id', $stay->room_id)
            ->where('status', self::STATUS_ACTIVA)
            ->count();

        if ($activeStay > 0) {
            return 'La habitacion ya tiene una estadia activa.';
        }

        $room = HotelRoom::where('empresa_id', $stay->empresa_id)->where('id', $stay->room_id)->first();
        if (is_null($room) || (int)$room->is_active != 1 || (int)$room->inv_producto_id <= 0) {
            return 'La habitacion no esta disponible para check-in.';
        }

        if ($room->status == HotelRoom::STATUS_DISPONIBLE) {
            return null;
        }

        if ($room->status == HotelRoom::STATUS_RESERVADA && !is_null(self::reservationForCheckIn($stay))) {
            return null;
        }

        return 'La habitacion no esta disponible para check-in.';
    }

    public static function getStayDatesError($stay)
    {
        if (empty($stay->check_in_at) || empty($stay->expected_check_out_at)) {
            return 'Debe ingresar check-in y salida esperada.';
        }

        $checkIn = strtotime($stay->check_in_at);
        $expectedCheckOut = strtotime($stay->expected_check_out_at);

        if ($checkIn === false || $expectedCheckOut === false) {
            return 'Las fechas de la estadia no son validas.';
        }

        if ($expectedCheckOut <= $checkIn) {
            return 'La salida esperada debe ser mayor que el check-in.';
        }

        return null;
    }

    public function stayDays()
    {
        return self::calculateStayDays($this->check_in_at, $this->expected_check_out_at);
    }

    public static function calculateStayDays($checkInAt, $expectedCheckOutAt)
    {
        $checkIn = strtotime($checkInAt);
        $expectedCheckOut = strtotime($expectedCheckOutAt);

        if ($checkIn === false || $expectedCheckOut === false || $expectedCheckOut <= $checkIn) {
            return 1;
        }

        return max(1, (int)ceil(($expectedCheckOut - $checkIn) / 86400));
    }

    private static function validateInvoicedRoomLineBeforeDateEdit($stay)
    {
        if (!$stay->isDirty('check_in_at') && !$stay->isDirty('expected_check_out_at') && !$stay->isDirty('room_id')) {
            return;
        }

        if (empty($stay->id)) {
            return;
        }

        $originalStay = self::where('id', $stay->id)->with('room')->first();
        if (is_null($originalStay)) {
            return;
        }

        $message = (new HotelService())->getEditDatesBlockMessage($originalStay);
        if ($message != '') {
            throw new \Exception($message);
        }
    }

    private static function syncPrimaryOrderRoomQuantityOnDateChange($stay)
    {
        if (!$stay->isDirty('check_in_at') && !$stay->isDirty('expected_check_out_at')) {
            return;
        }

        if ($stay->isDirty('room_id')) {
            return;
        }

        $oldDays = self::calculateStayDays($stay->getOriginal('check_in_at'), $stay->getOriginal('expected_check_out_at'));
        $newDays = self::calculateStayDays($stay->check_in_at, $stay->expected_check_out_at);

        if ($oldDays == $newDays) {
            return;
        }

        $order = HotelOrderHeader::where('empresa_id', $stay->empresa_id)
            ->where('stay_id', $stay->id)
            ->orderBy('id', 'ASC')
            ->first();

        if (is_null($order) || $order->status != HotelOrderHeader::STATUS_ABIERTO || !empty($order->invoice_type) || !empty($order->sales_doc_id) || !empty($order->pos_doc_id)) {
            return;
        }

        if ($order->lines()->count() != 1) {
            return;
        }

        $line = $order->lines()
            ->where('source_type', HotelOrderLine::SOURCE_ROOM)
            ->where('source_id', $stay->room_id)
            ->where('room_id', $stay->room_id)
            ->first();

        if (is_null($line)) {
            return;
        }

        $room = $stay->room;
        if (is_null($room) || (int)$line->producto_id != (int)$room->inv_producto_id) {
            return;
        }

        if (abs((float)$line->quantity - (float)$oldDays) > 0.01) {
            return;
        }

        (new HotelService())->updateLine($order, $line, array(
            'quantity' => $newDays,
        ));
    }

    private static function reservationForCheckIn($stay)
    {
        $date = substr($stay->check_in_at, 0, 10);

        return HotelReservation::where('empresa_id', $stay->empresa_id)
            ->where('room_id', $stay->room_id)
            ->where('cliente_id', $stay->main_cliente_id)
            ->whereNotIn('status', array(HotelReservation::STATUS_ANULADA, HotelReservation::STATUS_CUMPLIDA))
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
                DB::raw('IF(hotel_stays.expected_check_out_at IS NULL, 1, GREATEST(1, CEIL(TIMESTAMPDIFF(SECOND, hotel_stays.check_in_at, hotel_stays.expected_check_out_at) / 86400))) AS campo5'),
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
                DB::raw('IF(hotel_stays.expected_check_out_at IS NULL, 1, GREATEST(1, CEIL(TIMESTAMPDIFF(SECOND, hotel_stays.check_in_at, hotel_stays.expected_check_out_at) / 86400))) AS DIAS'),
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

    public static function normalizeDateTimeValue($value)
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
