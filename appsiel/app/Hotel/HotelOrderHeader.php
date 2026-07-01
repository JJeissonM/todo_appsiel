<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelOrderHeader extends Model
{
    const STATUS_ABIERTO = 'ABIERTO';
    const STATUS_FACTURADO = 'FACTURADO';
    const STATUS_ANULADO = 'ANULADO';

    const INVOICE_STANDARD = 'STANDARD';
    const INVOICE_POS = 'POS';

    protected $table = 'hotel_order_headers';

    protected $fillable = array('empresa_id', 'stay_id', 'cliente_id', 'document_number', 'order_date', 'status', 'invoice_type', 'sales_doc_id', 'pos_doc_id', 'lineas_registros_medios_recaudos', 'notes', 'created_by');

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Estadia', 'Habitacion', 'Cliente', 'Fecha', 'Estado', 'Factura');

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"hotel/orders/id_fila"}';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->empresa_id) && !empty($order->stay_id)) {
                $stay = HotelStay::find($order->stay_id);
                if ($stay) {
                    $order->empresa_id = $stay->empresa_id;
                    if (empty($order->cliente_id)) {
                        $order->cliente_id = $stay->main_cliente_id;
                    }
                }
            }

            if (empty($order->empresa_id) && Auth::check()) {
                $order->empresa_id = Auth::user()->empresa_id;
            }

            if (empty($order->created_by) && Auth::check()) {
                $order->created_by = Auth::user()->id;
            }

            if (empty($order->order_date)) {
                $order->order_date = date('Y-m-d H:i:s');
            }

            if (empty($order->status)) {
                $order->status = self::STATUS_ABIERTO;
            }
        });
    }

    public static function statuses()
    {
        return array(self::STATUS_ABIERTO, self::STATUS_FACTURADO, self::STATUS_ANULADO);
    }

    public function stay()
    {
        return $this->belongsTo('App\Hotel\HotelStay', 'stay_id');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'cliente_id');
    }

    public function lines()
    {
        return $this->hasMany('App\Hotel\HotelOrderLine', 'hotel_order_id');
    }

    public function canEditLines()
    {
        return $this->status == self::STATUS_ABIERTO;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return self::queryForIndex($search)
            ->select(
                DB::raw('IFNULL(hotel_order_headers.document_number, CONCAT("PED-", hotel_order_headers.id)) AS campo1'),
                DB::raw('CONCAT("#", hotel_stays.id) AS campo2'),
                'hotel_rooms.room_number AS campo3',
                'core_terceros.descripcion AS campo4',
                'hotel_order_headers.order_date AS campo5',
                'hotel_order_headers.status AS campo6',
                DB::raw('IFNULL(hotel_order_headers.invoice_type, "") AS campo7'),
                'hotel_order_headers.id AS campo8'
            )
            ->orderBy('hotel_order_headers.order_date', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_order_headers.document_number AS DOCUMENTO',
                'hotel_stays.id AS ESTADIA',
                'hotel_rooms.room_number AS HABITACION',
                'core_terceros.descripcion AS CLIENTE',
                'hotel_order_headers.order_date AS FECHA',
                'hotel_order_headers.status AS ESTADO',
                'hotel_order_headers.invoice_type AS TIPO_FACTURA'
            )
            ->toSql();
    }

    public static function tituloExport()
    {
        return 'PEDIDOS HOTELEROS';
    }

    public static function opciones_campo_select()
    {
        $query = self::orderBy('id', 'DESC');
        if (Auth::check()) {
            $query->where('empresa_id', Auth::user()->empresa_id);
        }

        $options = array('' => '');
        foreach ($query->get() as $order) {
            $label = $order->document_number ? $order->document_number : 'PED-' . $order->id;
            $options[$order->id] = $label . ' - ' . $order->status;
        }

        return $options;
    }

    public function validar_eliminacion($id)
    {
        $order = self::find($id);
        if ($order && $order->status == self::STATUS_FACTURADO) {
            return 'No se puede eliminar un pedido hotelero facturado.';
        }

        return 'ok';
    }

    private static function queryForIndex($search)
    {
        $query = self::leftJoin('hotel_stays', 'hotel_stays.id', '=', 'hotel_order_headers.stay_id')
            ->leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_stays.room_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'hotel_order_headers.cliente_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id');

        if (Auth::check()) {
            $query->where('hotel_order_headers.empresa_id', Auth::user()->empresa_id);
        }

        if ($search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('hotel_order_headers.document_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_order_headers.status', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_rooms.room_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('core_terceros.descripcion', 'LIKE', '%' . $search . '%');
            });
        }

        return $query;
    }
}
