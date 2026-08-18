<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelRoom;
use App\Hotel\HotelGuest;
use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelOrderLine;
use App\Hotel\HotelStay;
use App\Hotel\HotelStayGuest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HotelReportController extends Controller
{
    public function rooms(Request $request)
    {
        $rooms = HotelRoom::where('empresa_id', Auth::user()->empresa_id)
            ->with('product')
            ->orderBy('room_number')
            ->get();

        $html = view('hotel.reports.rooms', compact('rooms'))->render();
        $this->cacheReport($request, $html);

        return $html;
    }

    public function stays(Request $request)
    {
        $query = HotelStay::where('empresa_id', Auth::user()->empresa_id)
            ->with('room', 'mainGuest.tercero')
            ->orderBy('check_in_at', 'DESC');

        if ($request->fecha_desde != '') {
            $query->where('check_in_at', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->fecha_hasta != '') {
            $query->where('check_in_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $stays = $query->get();

        $html = view('hotel.reports.stays', compact('stays'))->render();
        $this->cacheReport($request, $html);

        return $html;
    }

    public function salesByRoom(Request $request)
    {
        $fechaDesde = trim((string)$request->fecha_desde);
        $fechaHasta = trim((string)$request->fecha_hasta);

        if (!$this->isValidReportDate($fechaDesde) || !$this->isValidReportDate($fechaHasta)) {
            return '<div class="alert alert-danger">Debe ingresar un rango de fechas válido.</div>';
        }

        if ($fechaDesde > $fechaHasta) {
            return '<div class="alert alert-danger">La fecha desde no puede ser posterior a la fecha hasta.</div>';
        }

        $mostrarDetalle = (string)$request->hotel_detalle !== '0';
        $ivaIncluido = (string)$request->hotel_iva_incluido !== '0';

        $lines = DB::table('hotel_order_lines AS hotel_line')
            ->join('hotel_order_headers AS hotel_order', 'hotel_order.id', '=', 'hotel_line.hotel_order_id')
            ->join('hotel_stays AS hotel_stay', 'hotel_stay.id', '=', 'hotel_order.stay_id')
            ->leftJoin('hotel_rooms AS hotel_room', 'hotel_room.id', '=', 'hotel_stay.room_id')
            ->leftJoin('vtas_clientes AS client', 'client.id', '=', 'hotel_stay.main_cliente_id')
            ->leftJoin('core_terceros AS third_party', 'third_party.id', '=', 'client.core_tercero_id')
            ->leftJoin('inv_productos AS product', 'product.id', '=', 'hotel_line.producto_id')
            ->leftJoin('inv_grupos AS product_group', 'product_group.id', '=', 'product.inv_grupo_id')
            ->where('hotel_line.empresa_id', Auth::user()->empresa_id)
            ->where('hotel_order.status', '<>', HotelOrderHeader::STATUS_ANULADO)
            ->where('hotel_stay.status', '<>', HotelStay::STATUS_ANULADA)
            ->where('hotel_order.order_date', '>=', $fechaDesde . ' 00:00:00')
            ->where('hotel_order.order_date', '<=', $fechaHasta . ' 23:59:59')
            ->select(
                'hotel_stay.room_id',
                'hotel_room.room_number',
                'hotel_stay.id AS stay_id',
                'hotel_stay.main_cliente_id',
                'hotel_stay.check_in_at',
                'hotel_stay.expected_check_out_at',
                'hotel_stay.check_out_at',
                'third_party.descripcion AS guest_name',
                'hotel_line.id AS line_id',
                'hotel_line.source_type',
                'hotel_line.line_total',
                'hotel_line.tax_value',
                'product.descripcion AS product_name',
                'product_group.descripcion AS product_group_name'
            )
            ->orderBy('hotel_room.room_number')
            ->orderBy('hotel_stay.check_in_at')
            ->orderBy('hotel_stay.id')
            ->orderBy('hotel_line.id')
            ->get();

        $rooms = array();
        $grandTotal = 0;

        foreach ($lines as $line) {
            $roomKey = !empty($line->room_id) ? (string)$line->room_id : 'room_without_id_' . $line->stay_id;
            $stayKey = (string)$line->stay_id;

            if (!isset($rooms[$roomKey])) {
                $rooms[$roomKey] = array(
                    'room_number' => !empty($line->room_number) ? $line->room_number : $line->room_id,
                    'stays' => array(),
                    'room_total' => 0,
                    'beverages_total' => 0,
                    'laundry_total' => 0,
                    'others_total' => 0,
                    'total' => 0,
                );
            }

            if (!isset($rooms[$roomKey]['stays'][$stayKey])) {
                $rooms[$roomKey]['stays'][$stayKey] = array(
                    'stay_id' => $line->stay_id,
                    'guest_name' => !empty($line->guest_name) ? $line->guest_name : 'Cliente #' . $line->main_cliente_id,
                    'check_in' => $this->formatReportDate($line->check_in_at),
                    'check_out' => $this->formatReportDate(!empty($line->check_out_at) ? $line->check_out_at : $line->expected_check_out_at),
                    'check_out_expected' => empty($line->check_out_at),
                    'days' => HotelStay::calculateStayDays($line->check_in_at, $line->expected_check_out_at),
                    'room_total' => 0,
                    'beverages_total' => 0,
                    'laundry_total' => 0,
                    'others_total' => 0,
                    'total' => 0,
                );
            }

            $value = (float)$line->line_total;
            if (!$ivaIncluido) {
                $value -= (float)$line->tax_value;
            }
            $value = max(0, $value);

            $category = $this->hotelSalesCategory($line);
            $rooms[$roomKey]['stays'][$stayKey][$category] += $value;
            $rooms[$roomKey]['stays'][$stayKey]['total'] += $value;
            $rooms[$roomKey][$category] += $value;
            $rooms[$roomKey]['total'] += $value;
            $grandTotal += $value;
        }

        $html = view('hotel.reports.sales_by_room', compact(
            'rooms',
            'grandTotal',
            'fechaDesde',
            'fechaHasta',
            'mostrarDetalle',
            'ivaIncluido'
        ))->render();
        $this->cacheReport($request, $html);

        return $html;
    }

    public function migration(Request $request)
    {
        $hotelGuestModelId = HotelGuest::hotelGuestModelId();
        $hotelGuestFieldIds = HotelGuest::hotelFieldIds();

        $query = HotelStayGuest::leftJoin('hotel_stays', 'hotel_stays.id', '=', 'hotel_stay_guests.stay_id')
            ->leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_stays.room_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'hotel_stay_guests.cliente_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->leftJoin('core_eav_valores as hotel_guest_fecha_nacimiento', function ($join) use ($hotelGuestModelId, $hotelGuestFieldIds) {
                $join->on('hotel_guest_fecha_nacimiento.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('hotel_guest_fecha_nacimiento.modelo_padre_id', '=', $hotelGuestModelId)
                    ->where('hotel_guest_fecha_nacimiento.modelo_entidad_id', '=', 0)
                    ->where('hotel_guest_fecha_nacimiento.core_campo_id', '=', isset($hotelGuestFieldIds[HotelGuest::FIELD_FECHA_NACIMIENTO]) ? $hotelGuestFieldIds[HotelGuest::FIELD_FECHA_NACIMIENTO] : 0);
            })
            ->leftJoin('core_eav_valores as hotel_guest_nacionalidad', function ($join) use ($hotelGuestModelId, $hotelGuestFieldIds) {
                $join->on('hotel_guest_nacionalidad.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('hotel_guest_nacionalidad.modelo_padre_id', '=', $hotelGuestModelId)
                    ->where('hotel_guest_nacionalidad.modelo_entidad_id', '=', 0)
                    ->where('hotel_guest_nacionalidad.core_campo_id', '=', isset($hotelGuestFieldIds[HotelGuest::FIELD_NACIONALIDAD]) ? $hotelGuestFieldIds[HotelGuest::FIELD_NACIONALIDAD] : 0);
            })
            ->leftJoin('core_eav_valores as hotel_guest_procedencia', function ($join) use ($hotelGuestModelId, $hotelGuestFieldIds) {
                $join->on('hotel_guest_procedencia.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('hotel_guest_procedencia.modelo_padre_id', '=', $hotelGuestModelId)
                    ->where('hotel_guest_procedencia.modelo_entidad_id', '=', 0)
                    ->where('hotel_guest_procedencia.core_campo_id', '=', isset($hotelGuestFieldIds[HotelGuest::FIELD_PROCEDENCIA]) ? $hotelGuestFieldIds[HotelGuest::FIELD_PROCEDENCIA] : 0);
            })
            ->leftJoin('core_eav_valores as hotel_guest_destino', function ($join) use ($hotelGuestModelId, $hotelGuestFieldIds) {
                $join->on('hotel_guest_destino.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('hotel_guest_destino.modelo_padre_id', '=', $hotelGuestModelId)
                    ->where('hotel_guest_destino.modelo_entidad_id', '=', 0)
                    ->where('hotel_guest_destino.core_campo_id', '=', isset($hotelGuestFieldIds[HotelGuest::FIELD_DESTINO]) ? $hotelGuestFieldIds[HotelGuest::FIELD_DESTINO] : 0);
            })
            ->leftJoin('core_eav_valores as hotel_guest_ocupacion', function ($join) use ($hotelGuestModelId, $hotelGuestFieldIds) {
                $join->on('hotel_guest_ocupacion.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('hotel_guest_ocupacion.modelo_padre_id', '=', $hotelGuestModelId)
                    ->where('hotel_guest_ocupacion.modelo_entidad_id', '=', 0)
                    ->where('hotel_guest_ocupacion.core_campo_id', '=', isset($hotelGuestFieldIds[HotelGuest::FIELD_OCUPACION]) ? $hotelGuestFieldIds[HotelGuest::FIELD_OCUPACION] : 0);
            })
            ->leftJoin('core_paises as pais_nacionalidad', 'pais_nacionalidad.id', '=', 'hotel_guest_nacionalidad.valor')
            ->leftJoin('core_ciudades as ciudad_tercero', 'ciudad_tercero.id', '=', 'core_terceros.codigo_ciudad')
            ->leftJoin('core_departamentos as depto_tercero', 'depto_tercero.id', '=', 'ciudad_tercero.core_departamento_id')
            ->leftJoin('core_ciudades as ciudad_procedencia', 'ciudad_procedencia.id', '=', 'hotel_guest_procedencia.valor')
            ->leftJoin('core_departamentos as depto_procedencia', 'depto_procedencia.id', '=', 'ciudad_procedencia.core_departamento_id')
            ->leftJoin('core_paises as pais_procedencia', 'pais_procedencia.id', '=', 'hotel_guest_procedencia.valor')
            ->leftJoin('core_ciudades as ciudad_destino', 'ciudad_destino.id', '=', 'hotel_guest_destino.valor')
            ->leftJoin('core_departamentos as depto_destino', 'depto_destino.id', '=', 'ciudad_destino.core_departamento_id')
            ->leftJoin('core_paises as pais_destino', 'pais_destino.id', '=', 'hotel_guest_destino.valor')
            ->where('hotel_stay_guests.empresa_id', Auth::user()->empresa_id)
            ->where('core_terceros.tipo', 'Persona natural')
            ->select(
                'hotel_stays.id AS stay_id',
                'hotel_rooms.room_number',
                'hotel_stays.check_in_at',
                'hotel_stays.check_out_at',
                'hotel_stays.expected_check_out_at',
                'core_terceros.codigo_ciudad',
                'core_tipos_docs_id.descripcion AS tipo_documento',
                'core_terceros.numero_identificacion',
                'core_terceros.nombre1',
                'core_terceros.otros_nombres',
                'core_terceros.apellido1',
                'core_terceros.apellido2',
                'core_terceros.descripcion',
                DB::raw('COALESCE(NULLIF(pais_nacionalidad.gentilicio, ""), pais_nacionalidad.descripcion, hotel_guest_nacionalidad.valor, "") AS nacionalidad'),
                'hotel_guest_fecha_nacimiento.valor AS fecha_nacimiento',
                'hotel_guest_ocupacion.valor AS ocupacion',
                DB::raw("COALESCE(NULLIF(CONCAT(COALESCE(ciudad_procedencia.descripcion,''), IF(depto_procedencia.descripcion IS NULL OR depto_procedencia.descripcion = '', '', CONCAT(', ', depto_procedencia.descripcion))), ''), NULLIF(pais_procedencia.descripcion, ''), NULLIF(CONCAT(COALESCE(ciudad_tercero.descripcion,''), IF(depto_tercero.descripcion IS NULL OR depto_tercero.descripcion = '', '', CONCAT(', ', depto_tercero.descripcion))), ''), hotel_guest_procedencia.valor, '') AS hotel_procedencia"),
                DB::raw("COALESCE(NULLIF(CONCAT(COALESCE(ciudad_destino.descripcion,''), IF(depto_destino.descripcion IS NULL OR depto_destino.descripcion = '', '', CONCAT(', ', depto_destino.descripcion))), ''), pais_destino.descripcion, hotel_guest_destino.valor, '') AS hotel_destino")
            )
            ->orderBy('hotel_stays.check_in_at', 'DESC');

        if ($request->fecha_desde != '') {
            $query->where('hotel_stays.check_in_at', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->fecha_hasta != '') {
            $query->where('hotel_stays.check_in_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $rows = $query->get();
        $codigoHotel = $request->codigo_hotel != '' ? $request->codigo_hotel : config('hotel.codigo_hotel', '');
        $tipoMovimiento = $request->tipo_movimiento != '' ? $request->tipo_movimiento : 'E';
        $lugarProcedencia = $request->lugar_procedencia != '' ? $request->lugar_procedencia : '';
        $lugarDestino = $request->lugar_destino != '' ? $request->lugar_destino : '';

        $html = view('hotel.reports.migration', compact('rows', 'codigoHotel', 'tipoMovimiento', 'lugarProcedencia', 'lugarDestino'))->render();
        $this->cacheReport($request, $html);

        return $html;
    }

    private function cacheReport(Request $request, $html)
    {
        if ($request->reporte_id != '') {
            Cache::put('pdf_reporte_' . $request->reporte_id, $html, 60);
        }
    }

    private function hotelSalesCategory($line)
    {
        $sourceType = strtoupper(trim((string)$line->source_type));
        $groupName = strtoupper(trim((string)$line->product_group_name));
        $productName = strtoupper(trim((string)$line->product_name));

        if ($sourceType == HotelOrderLine::SOURCE_ROOM || strpos($groupName, 'SERVICIO HOTELERO') !== false) {
            return 'room_total';
        }

        if (strpos($groupName, 'BEBIDA') !== false || strpos($groupName, 'MECATO') !== false ||
            strpos($productName, 'BEBIDA') !== false || strpos($productName, 'MECATO') !== false) {
            return 'beverages_total';
        }

        if (strpos($groupName, 'LAVANDER') !== false || strpos($productName, 'LAVANDER') !== false ||
            strpos($productName, 'ADICIONAL') !== false) {
            return 'laundry_total';
        }

        return 'others_total';
    }

    private function isValidReportDate($date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $parts = explode('-', $date);
        return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
    }

    private function formatReportDate($value)
    {
        $timestamp = strtotime($value);
        return $timestamp === false ? '' : date('d/m/Y', $timestamp);
    }
}
