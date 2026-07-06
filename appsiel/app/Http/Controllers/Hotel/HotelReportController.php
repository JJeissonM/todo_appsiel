<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelRoom;
use App\Hotel\HotelGuest;
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
            ->where('hotel_stay_guests.empresa_id', Auth::user()->empresa_id)
            ->select(
                'hotel_stays.id AS stay_id',
                'hotel_rooms.room_number',
                'hotel_stays.check_in_at',
                'hotel_stays.check_out_at',
                'hotel_stays.expected_check_out_at',
                'core_terceros.codigo_ciudad',
                'core_tipos_docs_id.abreviatura AS tipo_documento',
                'core_terceros.numero_identificacion',
                'core_terceros.nombre1',
                'core_terceros.otros_nombres',
                'core_terceros.apellido1',
                'core_terceros.apellido2',
                'core_terceros.descripcion',
                'hotel_guest_nacionalidad.valor AS codigo_nacionalidad',
                'hotel_guest_fecha_nacimiento.valor AS fecha_nacimiento',
                'hotel_guest_procedencia.valor AS hotel_procedencia',
                'hotel_guest_destino.valor AS hotel_destino'
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
}
