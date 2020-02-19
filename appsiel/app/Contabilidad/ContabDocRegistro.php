<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;

class ContabDocRegistro extends Model
{
    //protected $table = 'teso_doc_registros_recaudos';

    protected $fillable = ['contab_doc_encabezado_id','contab_cuenta_id','valor_debito','valor_credito','detalle_operacion','estado'];

    public $campos_invisibles_linea_registro = ['cuenta_id','tercero_id','valor_db','valor_cr']; // 4 campos

    public $campos_visibles_linea_registro = [ 
    											['Cuenta',''],
    											['Tercero',''],
    											['Detalle',''],
    											['Débito',''],
                                                ['Crédito',''],
    											['&nbsp;','10px']
    										]; // 6 campos
}
