<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

class TesoDocRegistroRecaudo extends Model
{
    protected $table = 'teso_doc_registros_recaudos';

    protected $fillable = ['teso_encabezado_recaudo_id','teso_medio_recaudo_id','teso_motivo_id','teso_caja_id','teso_cuenta_bancaria_id','valor_total','descripcion','estado'];
}
