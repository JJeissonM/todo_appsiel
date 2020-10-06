<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class DescuentoCartera extends Model
{
  protected $table = 'cxc_doc_encabezados';
  protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','fecha_vencimiento','core_empresa_id','core_tercero_id','tipo_documento','documento_soporte','descripcion','valor_total','estado','creado_por','modificado_por','codigo_referencia_tercero'];
  
  
}