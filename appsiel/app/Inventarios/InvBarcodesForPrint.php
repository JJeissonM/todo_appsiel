<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

class InvBarcodesForPrint extends Model
{
    protected $table = 'inv_barcodes_for_print'; 

    protected $fillable = [ 'item_id', 'label', 'barcode', 'reference', 'uom_1', 'size', 'supplier_code' ];

    public function item()
    {
        return $this->belongsTo(InvProducto::class, 'inv_item_id');
    }

}
