<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;

class ContabDocRegistro extends Model
{
    //protected $table = 'teso_doc_registros_recaudos';

    protected $fillable = [ 'contab_doc_encabezado_id', 'contab_cuenta_id', 'core_tercero_id', 'valor_debito', 'valor_credito', 'detalle_operacion', 'estado'];

    public $campos_invisibles_linea_registro = ['cuenta_id','tercero_id','valor_db','valor_cr']; // 4 campos

    public $campos_visibles_linea_registro = [ 
    											['Cuenta',''],
    											['Tercero',''],
    											['Detalle',''],
    											['Débito',''],
                                                ['Crédito',''],
    											['&nbsp;','10px']
    										]; // 6 campos



    public static function get_registros_impresion( $doc_encabezado_id )
    {
        return ContabDocRegistro::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_doc_registros.contab_cuenta_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_doc_registros.core_tercero_id')
                            ->where('contab_doc_registros.contab_doc_encabezado_id',$doc_encabezado_id)
                            ->select(
                                        DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero' ),
                                        DB::raw( 'CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS cuenta' ),
                                        'core_terceros.numero_identificacion',
                                        'core_terceros.id AS tercero_id',
                                        'contab_doc_registros.valor_debito',
                                        'contab_doc_registros.valor_credito',
                                        'contab_doc_registros.detalle_operacion',
                                        'contab_cuentas.id AS cuenta_id',
                                        'contab_cuentas.codigo AS cuenta_codigo')
                            ->get();
    }
}