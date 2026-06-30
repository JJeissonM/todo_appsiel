<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelStayGuest extends Model
{
    protected $table = 'hotel_stay_guests';

    protected $fillable = array('empresa_id', 'stay_id', 'cliente_id', 'is_main_guest', 'relationship');

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estadia', 'Habitacion', 'Cliente', 'Principal', 'Relacion');

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($guest) {
            if (empty($guest->empresa_id) && !empty($guest->stay_id)) {
                $stay = HotelStay::find($guest->stay_id);
                if ($stay) {
                    $guest->empresa_id = $stay->empresa_id;
                }
            }

            if (empty($guest->empresa_id) && Auth::check()) {
                $guest->empresa_id = Auth::user()->empresa_id;
            }

            if ($guest->is_main_guest === null) {
                $guest->is_main_guest = 0;
            }
        });
    }

    public function stay()
    {
        return $this->belongsTo('App\Hotel\HotelStay', 'stay_id');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'cliente_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return self::queryForIndex($search)
            ->select(
                DB::raw('CONCAT("#", hotel_stays.id) AS campo1'),
                'hotel_rooms.room_number AS campo2',
                'core_terceros.descripcion AS campo3',
                DB::raw('IF(hotel_stay_guests.is_main_guest = 1, "Si", "No") AS campo4'),
                'hotel_stay_guests.relationship AS campo5',
                'hotel_stay_guests.id AS campo6'
            )
            ->orderBy('hotel_stay_guests.id', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_stays.id AS ESTADIA',
                'hotel_rooms.room_number AS HABITACION',
                'core_terceros.descripcion AS CLIENTE',
                'hotel_stay_guests.is_main_guest AS PRINCIPAL',
                'hotel_stay_guests.relationship AS RELACION'
            )
            ->toSql();
    }

    public static function tituloExport()
    {
        return 'HUESPEDES DE ESTADIA HOTELERA';
    }

    public static function opciones_campo_select()
    {
        $query = self::leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'hotel_stay_guests.cliente_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->select('hotel_stay_guests.id', 'core_terceros.descripcion')
            ->orderBy('hotel_stay_guests.id', 'DESC');

        if (Auth::check()) {
            $query->where('hotel_stay_guests.empresa_id', Auth::user()->empresa_id);
        }

        $options = array('' => '');
        foreach ($query->get() as $guest) {
            $options[$guest->id] = '#' . $guest->id . ' - ' . $guest->descripcion;
        }

        return $options;
    }

    public function validar_eliminacion($id)
    {
        $guest = self::find($id);
        if ($guest && $guest->is_main_guest) {
            return 'No se puede eliminar el huesped principal de la estadia.';
        }

        return 'ok';
    }

    private static function queryForIndex($search)
    {
        $query = self::leftJoin('hotel_stays', 'hotel_stays.id', '=', 'hotel_stay_guests.stay_id')
            ->leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_stays.room_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'hotel_stay_guests.cliente_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id');

        if (Auth::check()) {
            $query->where('hotel_stay_guests.empresa_id', Auth::user()->empresa_id);
        }

        if ($search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('hotel_rooms.room_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('core_terceros.descripcion', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_stay_guests.relationship', 'LIKE', '%' . $search . '%');
            });
        }

        return $query;
    }
}
