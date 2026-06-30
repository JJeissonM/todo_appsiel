<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelOrderLine extends Model
{
    const SOURCE_ROOM = 'ROOM';
    const SOURCE_PRODUCT = 'PRODUCT';
    const SOURCE_SERVICE = 'SERVICE';
    const SOURCE_MANUAL = 'MANUAL';

    protected $table = 'hotel_order_lines';

    protected $fillable = array('empresa_id', 'hotel_order_id', 'producto_id', 'room_id', 'description', 'quantity', 'unit_price', 'discount', 'tax_value', 'line_total', 'source_type', 'source_id');

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Pedido', 'Producto', 'Habitacion', 'Cantidad', 'Precio', 'Descuento', 'Impuesto', 'Total');

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($line) {
            self::prepareLine($line);
        });

        static::updating(function ($line) {
            self::prepareLine($line);
        });
    }

    public static function sourceTypes()
    {
        return array(self::SOURCE_ROOM, self::SOURCE_PRODUCT, self::SOURCE_SERVICE, self::SOURCE_MANUAL);
    }

    public static function calculateTotal($quantity, $unitPrice, $discount, $taxValue)
    {
        return ($quantity * $unitPrice) - $discount + $taxValue;
    }

    private static function prepareLine($line)
    {
        if (empty($line->empresa_id) && !empty($line->hotel_order_id)) {
            $order = HotelOrderHeader::find($line->hotel_order_id);
            if ($order) {
                $line->empresa_id = $order->empresa_id;
            }
        }

        if (empty($line->empresa_id) && Auth::check()) {
            $line->empresa_id = Auth::user()->empresa_id;
        }

        if (empty($line->quantity)) {
            $line->quantity = 1;
        }

        if ($line->unit_price === null) {
            $line->unit_price = 0;
        }

        if ($line->discount === null) {
            $line->discount = 0;
        }

        if ($line->tax_value === null) {
            $line->tax_value = 0;
        }

        if (empty($line->source_type)) {
            $line->source_type = self::SOURCE_MANUAL;
        }

        $line->line_total = self::calculateTotal((float)$line->quantity, (float)$line->unit_price, (float)$line->discount, (float)$line->tax_value);
    }

    public function order()
    {
        return $this->belongsTo('App\Hotel\HotelOrderHeader', 'hotel_order_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Inventarios\InvProducto', 'producto_id');
    }

    public function room()
    {
        return $this->belongsTo('App\Hotel\HotelRoom', 'room_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return self::queryForIndex($search)
            ->select(
                DB::raw('CONCAT("PED-", hotel_order_lines.hotel_order_id) AS campo1'),
                'inv_productos.descripcion AS campo2',
                'hotel_rooms.room_number AS campo3',
                'hotel_order_lines.quantity AS campo4',
                'hotel_order_lines.unit_price AS campo5',
                'hotel_order_lines.discount AS campo6',
                'hotel_order_lines.tax_value AS campo7',
                'hotel_order_lines.line_total AS campo8',
                'hotel_order_lines.id AS campo9'
            )
            ->orderBy('hotel_order_lines.id', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_order_lines.hotel_order_id AS PEDIDO',
                'inv_productos.descripcion AS PRODUCTO',
                'hotel_rooms.room_number AS HABITACION',
                'hotel_order_lines.quantity AS CANTIDAD',
                'hotel_order_lines.unit_price AS PRECIO',
                'hotel_order_lines.discount AS DESCUENTO',
                'hotel_order_lines.tax_value AS IMPUESTO',
                'hotel_order_lines.line_total AS TOTAL'
            )
            ->toSql();
    }

    public static function tituloExport()
    {
        return 'LINEAS DE PEDIDOS HOTELEROS';
    }

    public static function opciones_campo_select()
    {
        $query = self::leftJoin('inv_productos', 'inv_productos.id', '=', 'hotel_order_lines.producto_id')
            ->select('hotel_order_lines.id', 'inv_productos.descripcion')
            ->orderBy('hotel_order_lines.id', 'DESC');

        if (Auth::check()) {
            $query->where('hotel_order_lines.empresa_id', Auth::user()->empresa_id);
        }

        $options = array('' => '');
        foreach ($query->get() as $line) {
            $options[$line->id] = '#' . $line->id . ' - ' . $line->descripcion;
        }

        return $options;
    }

    public function validar_eliminacion($id)
    {
        $line = self::find($id);
        if ($line && $line->order && $line->order->status != HotelOrderHeader::STATUS_ABIERTO) {
            return 'No se puede eliminar una linea de un pedido facturado o anulado.';
        }

        return 'ok';
    }

    private static function queryForIndex($search)
    {
        $query = self::leftJoin('hotel_order_headers', 'hotel_order_headers.id', '=', 'hotel_order_lines.hotel_order_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'hotel_order_lines.producto_id')
            ->leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_order_lines.room_id');

        if (Auth::check()) {
            $query->where('hotel_order_lines.empresa_id', Auth::user()->empresa_id);
        }

        if ($search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('inv_productos.descripcion', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_rooms.room_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_order_lines.description', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_order_lines.source_type', 'LIKE', '%' . $search . '%');
            });
        }

        return $query;
    }
}
