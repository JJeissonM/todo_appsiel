<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\VentasPos\FacturaPos;
use App\VentasPos\DocRegistro;

class HotelOrderHeader extends Model
{
    const STATUS_ABIERTO = 'ABIERTO';
    const STATUS_FACTURADO = 'FACTURADO';
    const STATUS_ANULADO = 'ANULADO';

    const INVOICE_STANDARD = 'STANDARD';
    const INVOICE_POS = 'POS';

    protected $table = 'hotel_order_headers';

    protected $fillable = array('empresa_id', 'stay_id', 'cliente_id', 'pdv_id', 'document_number', 'order_date', 'status', 'invoice_type', 'sales_doc_id', 'pos_doc_id', 'notes', 'created_by');

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Estadía', 'Habitación', 'Cliente', 'Factura', 'Estado');

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

    public function pdv()
    {
        return $this->belongsTo('App\VentasPos\Pdv', 'pdv_id');
    }

    public function lines()
    {
        return $this->hasMany('App\Hotel\HotelOrderLine', 'hotel_order_id');
    }

    public function salesInvoice()
    {
        return $this->belongsTo('App\Ventas\VtasDocEncabezado', 'sales_doc_id');
    }

    public function posInvoice()
    {
        return $this->belongsTo('App\VentasPos\FacturaPos', 'pos_doc_id');
    }

    public function canEditLines()
    {
        return $this->status == self::STATUS_ABIERTO;
    }

    public function creador_por()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_order_headers.order_date AS campo1',
                DB::raw('IFNULL(hotel_order_headers.document_number, CONCAT("PED-", hotel_order_headers.id)) AS campo2'),
                DB::raw('CONCAT("#", hotel_stays.id) AS campo3'),
                'hotel_rooms.room_number AS campo4',
                'core_terceros.descripcion AS campo5',
                DB::raw('CASE WHEN hotel_order_headers.invoice_type = "POS" AND hotel_order_headers.pos_doc_id IS NOT NULL THEN CONCAT(IFNULL(pos_tipo_doc.prefijo, ""), " ", IFNULL(pos_doc.consecutivo, hotel_order_headers.pos_doc_id)) WHEN hotel_order_headers.invoice_type = "STANDARD" AND hotel_order_headers.sales_doc_id IS NOT NULL THEN CONCAT("Ventas ", IFNULL(sales_tipo_doc.prefijo, ""), " ", IFNULL(sales_doc.consecutivo, hotel_order_headers.sales_doc_id)) ELSE "" END AS campo6'),
                'hotel_order_headers.status AS campo7',
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
                DB::raw('CASE WHEN hotel_order_headers.invoice_type = "POS" AND hotel_order_headers.pos_doc_id IS NOT NULL THEN CONCAT(IFNULL(pos_tipo_doc.prefijo, ""), " ", IFNULL(pos_doc.consecutivo, hotel_order_headers.pos_doc_id)) WHEN hotel_order_headers.invoice_type = "STANDARD" AND hotel_order_headers.sales_doc_id IS NOT NULL THEN CONCAT("Ventas ", IFNULL(sales_tipo_doc.prefijo, ""), " ", IFNULL(sales_doc.consecutivo, hotel_order_headers.sales_doc_id)) ELSE "" END AS FACTURA')
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

    public function invoiceLabel()
    {
        if ($this->invoice_type == self::INVOICE_POS && !empty($this->pos_doc_id)) {
            $doc = $this->posInvoice;
            if (is_null($doc)) {
                $doc = FacturaPos::find($this->pos_doc_id);
            }

            if (!is_null($doc) && !is_null($doc->tipo_documento_app)) {
                return $doc->tipo_documento_app->prefijo . ' ' . $doc->consecutivo;
            }

            return $this->pos_doc_id;
        }

        if ($this->invoice_type == self::INVOICE_STANDARD && !empty($this->sales_doc_id)) {
            $doc = $this->salesInvoice;
            if (is_null($doc)) {
                $doc = VtasDocEncabezado::find($this->sales_doc_id);
            }

            if (!is_null($doc) && !is_null($doc->tipo_documento_app)) {
                return $doc->tipo_documento_app->prefijo . ' ' . $doc->consecutivo;
            }

            return $this->sales_doc_id;
        }

        return '';
    }

    public function invoiceUrl()
    {
        if ($this->invoice_type == self::INVOICE_POS && !empty($this->pos_doc_id)) {
            return url('pos_factura/' . $this->pos_doc_id . '?id=20&id_modelo=230&id_transaccion=47');
        }

        if ($this->invoice_type == self::INVOICE_STANDARD && !empty($this->sales_doc_id)) {
            return url('ventas/' . $this->sales_doc_id . '?id=13&id_modelo=' . config('ventas.factura_ventas_modelo_id', 139) . '&id_transaccion=' . config('ventas.factura_ventas_tipo_transaccion_id', 23));
        }

        return '';
    }

    public static function reopenOrdersForCancelledSalesInvoice($salesDocId, $relatedPosDocId = null)
    {
        return self::reopenOrdersForCancelledInvoice((int)$salesDocId, (int)$relatedPosDocId);
    }

    public static function reopenOrdersForCancelledPosInvoice($posDocId)
    {
        return self::reopenOrdersForCancelledInvoice(null, (int)$posDocId);
    }

    protected static function reopenOrdersForCancelledInvoice($salesDocId = null, $posDocId = null)
    {
        if (!self::hotelTablesAreAvailable()) {
            return 0;
        }

        $salesDocId = (int)$salesDocId;
        $posDocId = (int)$posDocId;

        if ($salesDocId <= 0 && $posDocId <= 0) {
            return 0;
        }

        $query = self::where('status', self::STATUS_FACTURADO)
            ->where(function ($q) use ($salesDocId, $posDocId) {
                if ($salesDocId > 0) {
                    $q->orWhere('sales_doc_id', $salesDocId);
                }

                if ($posDocId > 0) {
                    $q->orWhere('pos_doc_id', $posDocId);
                }
            });

        $count = 0;
        foreach ($query->get() as $order) {
            self::restoreMissingLinesFromCancelledInvoice($order, $salesDocId, $posDocId);

            $order->status = self::STATUS_ABIERTO;

            if ($salesDocId > 0 && (int)$order->sales_doc_id == $salesDocId) {
                $order->sales_doc_id = null;
            }

            if ($posDocId > 0 && (int)$order->pos_doc_id == $posDocId) {
                $order->pos_doc_id = null;
            }

            if (empty($order->sales_doc_id) && empty($order->pos_doc_id)) {
                $order->invoice_type = null;
            }

            $order->save();
            $count++;
        }

        return $count;
    }

    protected static function restoreMissingLinesFromCancelledInvoice($order, $salesDocId = null, $posDocId = null)
    {
        if ($order->lines()->count() > 0) {
            return;
        }

        $records = array();
        if ($posDocId > 0 && (int)$order->pos_doc_id == (int)$posDocId && Schema::hasTable('vtas_pos_doc_registros')) {
            $records = DocRegistro::where('vtas_pos_doc_encabezado_id', (int)$posDocId)->get();
        }

        if (count($records) == 0 && $salesDocId > 0 && (int)$order->sales_doc_id == (int)$salesDocId && Schema::hasTable('vtas_doc_registros')) {
            $records = VtasDocRegistro::where('vtas_doc_encabezado_id', (int)$salesDocId)->get();
        }

        if (count($records) == 0) {
            return;
        }

        $room = null;
        $stay = HotelStay::find((int)$order->stay_id);
        if (!is_null($stay) && (int)$stay->room_id > 0) {
            $room = HotelRoom::find((int)$stay->room_id);
        }

        foreach ($records as $record) {
            $productoId = (int)self::valueFromModel($record, 'inv_producto_id', 0);
            if ($productoId <= 0) {
                continue;
            }

            $quantity = (float)self::valueFromModel($record, 'cantidad', 1);
            $unitPrice = (float)self::valueFromModel($record, 'precio_unitario', 0);
            $discount = (float)self::valueFromModel($record, 'valor_total_descuento', 0);
            $taxValue = (float)self::valueFromModel($record, 'valor_impuesto', 0) * $quantity;
            $lineTotal = (float)self::valueFromModel($record, 'precio_total', HotelOrderLine::calculateTotal($quantity, $unitPrice, $discount, $taxValue));
            $bodegaId = (int)self::valueFromModel($record, 'inv_bodega_id', 0);

            if ($bodegaId <= 0 && !is_null($room) && (int)$room->inv_bodega_id > 0) {
                $bodegaId = (int)$room->inv_bodega_id;
            }

            $description = self::productDescription($productoId);
            $sourceType = HotelOrderLine::SOURCE_PRODUCT;
            $sourceId = $productoId;

            if (!is_null($room) && (int)$room->inv_producto_id == $productoId) {
                $sourceType = HotelOrderLine::SOURCE_ROOM;
                $sourceId = (int)$room->id;
            }

            HotelOrderLine::create(array(
                'empresa_id' => $order->empresa_id,
                'hotel_order_id' => $order->id,
                'producto_id' => $productoId,
                'room_id' => !is_null($room) ? (int)$room->id : null,
                'inv_bodega_id' => $bodegaId > 0 ? $bodegaId : null,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'tax_value' => $taxValue,
                'line_total' => $lineTotal,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
            ));
        }
    }

    protected static function valueFromModel($model, $key, $default = null)
    {
        if (is_object($model) && method_exists($model, 'getAttributes')) {
            $attributes = $model->getAttributes();
            if (array_key_exists($key, $attributes)) {
                return $attributes[$key];
            }
        }

        if (is_object($model) && isset($model->$key)) {
            return $model->$key;
        }

        return $default;
    }

    protected static function productDescription($productoId)
    {
        $producto = DB::table('inv_productos')->where('id', (int)$productoId)->first();
        if (!is_null($producto) && isset($producto->descripcion)) {
            return $producto->descripcion;
        }

        return 'Producto ' . (int)$productoId;
    }

    protected static function hotelTablesAreAvailable()
    {
        $enabledValue = strtolower((string)env('HOTEL_MODULE_ENABLED', false));
        $enabled = in_array($enabledValue, array('1', 'true', 'yes', 'on'));

        return $enabled && Schema::hasTable('hotel_order_headers') && Schema::hasTable('hotel_order_lines');
    }

    private static function queryForIndex($search)
    {
        $query = self::leftJoin('hotel_stays', 'hotel_stays.id', '=', 'hotel_order_headers.stay_id')
            ->leftJoin('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_stays.room_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'hotel_order_headers.cliente_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('vtas_pos_doc_encabezados AS pos_doc', 'pos_doc.id', '=', 'hotel_order_headers.pos_doc_id')
            ->leftJoin('core_tipos_docs_apps AS pos_tipo_doc', 'pos_tipo_doc.id', '=', 'pos_doc.core_tipo_doc_app_id')
            ->leftJoin('vtas_doc_encabezados AS sales_doc', 'sales_doc.id', '=', 'hotel_order_headers.sales_doc_id')
            ->leftJoin('core_tipos_docs_apps AS sales_tipo_doc', 'sales_tipo_doc.id', '=', 'sales_doc.core_tipo_doc_app_id');

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
