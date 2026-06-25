<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;

class HotelOrderHeader extends Model
{
    const STATUS_ABIERTO = 'ABIERTO';
    const STATUS_FACTURADO = 'FACTURADO';
    const STATUS_ANULADO = 'ANULADO';

    const INVOICE_STANDARD = 'STANDARD';
    const INVOICE_POS = 'POS';

    protected $table = 'hotel_order_headers';

    protected $fillable = array('empresa_id', 'stay_id', 'cliente_id', 'document_number', 'order_date', 'status', 'invoice_type', 'sales_doc_id', 'pos_doc_id', 'notes', 'created_by');

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
}
