<?php

namespace App\Hotel;

use App\CxC\CxcMovimiento;
use App\Hotel\Services\HotelReservationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function validar_datos_creacion($request, $controller)
    {
        $this->validarDatosFormulario($request, $controller);
    }

    public function validar_datos_actualizacion($request, $controller, $id)
    {
        $this->validarDatosFormulario($request, $controller, $id);
    }

    private function validarDatosFormulario($request, $controller, $id = null)
    {
        $controller->validate($request, array(
            'cliente_id' => 'required',
            'room_id' => 'required',
            'reserved_from' => 'required',
            'reserved_until' => 'required',
        ), array(
            'cliente_id.required' => 'Debe seleccionar el huesped.',
            'room_id.required' => 'Debe seleccionar una habitacion.',
            'reserved_from.required' => 'Debe ingresar la fecha y hora de inicio de la reserva.',
            'reserved_until.required' => 'Debe ingresar la fecha y hora de finalizacion de la reserva.',
        ));

        $validator = \Validator::make($request->all(), array());
        $validator->after(function ($validator) use ($request, $id) {
            $service = app(HotelReservationService::class);
            $reservation = new self;
            $reservation->id = $id;
            $reservation->empresa_id = $request->empresa_id;
            if (empty($reservation->empresa_id) && Auth::check()) {
                $reservation->empresa_id = Auth::user()->empresa_id;
            }

            $reservation->cliente_id = $request->cliente_id;
            $reservation->room_id = $request->room_id;
            $reservation->reserved_from = $request->reserved_from;
            $reservation->reserved_until = $request->reserved_until;
            $reservation->status = in_array($request->status, self::statuses()) ? $request->status : self::STATUS_ACTIVA;

            $service->prepare($reservation);
            $message = $service->getPreparationError($reservation);
            if (is_null($message)) {
                $message = $service->getAvailabilityError($reservation);
            }

            if (!is_null($message)) {
                $validator->errors()->add('reserved_from', $message);
            }
        });

        $controller->validateWith($validator, $request);
    }

    private static function prepareReservation($reservation)
    {
        if (empty($reservation->empresa_id) && Auth::check()) {
            $reservation->empresa_id = Auth::user()->empresa_id;
        }

        if (empty($reservation->created_by) && Auth::check()) {
            $reservation->created_by = Auth::user()->id;
        }

        if (empty($reservation->status) || !in_array($reservation->status, self::statuses())) {
            $reservation->status = self::STATUS_ACTIVA;
        }

        $service = app(HotelReservationService::class);
        $service->prepare($reservation);
        $message = $service->getPreparationError($reservation);

        if (!is_null($message)) {
            throw new \Exception($message);
        }
    }

    private static function validateAvailability($reservation)
    {
        $message = app(HotelReservationService::class)->getAvailabilityError($reservation);

        if (!is_null($message)) {
            throw new \Exception($message);
        }
    }

    public function store_adicional($datos, $registro)
    {
        $registro->syncRoomStatus();
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
                $lista_campos[$key]['opciones'] = self::roomOptionsForReservation();
            }

            if ($campo['name'] == 'cliente_id' && Input::get('cliente_id') != '') {
                $lista_campos[$key]['value'] = Input::get('cliente_id');
            }

            if (in_array($campo['name'], array('cliente_id', 'room_id', 'reserved_from', 'reserved_until'))) {
                $lista_campos[$key]['requerido'] = 1;
                if (!isset($lista_campos[$key]['atributos']) || !is_array($lista_campos[$key]['atributos'])) {
                    $lista_campos[$key]['atributos'] = array();
                }
                $lista_campos[$key]['atributos']['required'] = 'required';
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

    public static function roomOptionsForReservation()
    {
        $query = HotelRoom::where('is_active', 1)
            ->where('status', '<>', HotelRoom::STATUS_BLOQUEADA)
            ->orderBy('room_number');

        if (Auth::check()) {
            $query->where('empresa_id', Auth::user()->empresa_id);
        }

        $options = array('' => '');
        foreach ($query->get() as $room) {
            $options[$room->id] = $room->room_number . ' - ' . $room->room_type;
        }

        return $options;
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        return $this->setCustomerAutocompleteField($lista_campos);
    }

    public function show_adicional($lista_campos, $registro)
    {
        foreach ($lista_campos as $key => $campo) {
            if (!isset($campo['name'])) {
                continue;
            }

            if ($campo['name'] == 'cliente_id') {
                $clienteLabel = $this->getClienteLabel($registro);
                $lista_campos[$key]['value'] = $clienteLabel;
                $lista_campos[$key]['show_value'] = $clienteLabel;
            }

            if ($campo['name'] == 'room_id') {
                $roomLabel = $this->getRoomLabel($registro);
                $lista_campos[$key]['value'] = $roomLabel;
                $lista_campos[$key]['show_value'] = $roomLabel;
            }
        }

        return $lista_campos;
    }

    public function get_botones_adicionales_show($registro, $variables_url)
    {
        $cliente = $registro->cliente;
        if (is_null($cliente) || is_null($cliente->tercero)) {
            return array();
        }

        $url = 'tesoreria/recaudos/create?id=3'
            . '&id_modelo=46&id_transaccion=8'
            . '&core_tercero_id=' . $cliente->core_tercero_id
            . '&cliente_text=' . rawurlencode($this->getClienteLabel($registro))
            . '&hotel_reservation_id=' . $registro->id;

        $btn = new \stdClass();
        $btn->url = $url;
        $btn->title = 'Registrar anticipo';
        $btn->color_bootstrap = 'success';
        $btn->faicon = 'money';
        $btn->size = 'md';
        $btn->tag_html = 'a';
        $btn->target = '_blank';

        return array(new \App\Sistema\Html\Boton($btn));
    }

    private function setCustomerAutocompleteField($lista_campos)
    {
        foreach ($lista_campos as $key => $campo) {
            if (isset($campo['name']) && $campo['name'] == 'cliente_id') {
                $lista_campos[$key]['tipo'] = 'cliente_autocomplete';
                $lista_campos[$key]['opciones'] = '';
                $lista_campos[$key]['atributos'] = array('class' => 'form-control');
            }
        }

        return $lista_campos;
    }

    private function getClienteLabel($reservation)
    {
        $cliente = $reservation->cliente;
        if (is_null($cliente) || is_null($cliente->tercero)) {
            return '';
        }

        return trim($cliente->tercero->numero_identificacion . ' - ' . $cliente->tercero->descripcion);
    }

    private function getRoomLabel($reservation)
    {
        $room = $reservation->room;
        if (is_null($room)) {
            return '';
        }

        return trim($room->room_number . ' - ' . $room->room_type);
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
        $message = $this->getPendingAdvanceCancelBlockMessage();
        if (!is_null($message)) {
            throw new \Exception($message);
        }

        $this->status = self::STATUS_ANULADA;
        $this->save();
        $this->releaseRoomIfNeeded();
    }

    public function getPendingAdvanceCancelBlockMessage()
    {
        $cliente = $this->cliente;

        if (is_null($cliente) || empty($cliente->core_tercero_id)) {
            return null;
        }

        $advance = CxcMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
            ->leftJoin('cxc_abonos AS abonos_cruce', function ($join) {
                $join->on('abonos_cruce.core_empresa_id', '=', 'cxc_movimientos.core_empresa_id')
                    ->on('abonos_cruce.core_tercero_id', '=', 'cxc_movimientos.core_tercero_id')
                    ->on('abonos_cruce.core_tipo_transaccion_id', '=', 'cxc_movimientos.core_tipo_transaccion_id')
                    ->on('abonos_cruce.core_tipo_doc_app_id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
                    ->on('abonos_cruce.consecutivo', '=', 'cxc_movimientos.consecutivo');
            })
            ->where('cxc_movimientos.core_empresa_id', $this->empresa_id)
            ->where('cxc_movimientos.core_tercero_id', $cliente->core_tercero_id)
            ->where('cxc_movimientos.saldo_pendiente', '<', -0.1)
            ->where('cxc_movimientos.estado', '<>', 'Anulado')
            ->whereNull('abonos_cruce.id')
            ->select('cxc_movimientos.id', DB::raw('CONCAT(IFNULL(core_tipos_docs_apps.prefijo, ""), " ", cxc_movimientos.consecutivo) AS documento'))
            ->first();

        if (is_null($advance)) {
            return null;
        }

        $documento = trim($advance->documento);
        if ($documento == '') {
            $documento = 'CxC #' . $advance->id;
        }

        return 'No se puede anular la reserva porque el huesped tiene un anticipo (' . $documento . '). Primero debes anular el anticipo para luego anular la reserva.';
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
        if (!in_array($this->status, array(self::STATUS_ANULADA, self::STATUS_CUMPLIDA)) && $this->coversMoment(date('Y-m-d H:i:s'))) {
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

        $now = date('Y-m-d H:i:s');
        $hasTodayReservation = self::where('empresa_id', $this->empresa_id)
            ->where('room_id', $this->room_id)
            ->whereNotIn('status', array(self::STATUS_ANULADA, self::STATUS_CUMPLIDA))
            ->where('reserved_from', '<=', $now)
            ->where('reserved_until', '>', $now)
            ->count() > 0;

        if (!$hasTodayReservation) {
            $room->status = HotelRoom::STATUS_DISPONIBLE;
            $room->save();
        }
    }

    public function coversMoment($moment)
    {
        return app(HotelReservationService::class)->coversMoment($this, $moment);
    }

    public function coversDate($date)
    {
        return $this->coversMoment($date);
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
