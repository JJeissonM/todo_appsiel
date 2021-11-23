<?php

namespace App\Nomina\Services;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\NomContrato;
use App\Nomina\NomDocRegistro;

class LineaDocumentoService
{
    public function total_devengos_modo_liquidacion_lapso( $lapso, $modo_liquidacion_id )
    {
        return NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                ->where( 'nom_conceptos.modo_liquidacion_id', $modo_liquidacion_id )
                                ->whereBetween( 'nom_doc_registros.fecha', [ $lapso->fecha_inicial, $lapso->fecha_final ] )
                                ->sum('nom_doc_registros.valor_devengo');
    }

    public function total_deducciones_modo_liquidacion_lapso( $lapso, $modo_liquidacion_id )
    {
        return NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                ->where( 'nom_conceptos.modo_liquidacion_id', $modo_liquidacion_id )
                                ->whereBetween( 'nom_doc_registros.fecha', [ $lapso->fecha_inicial, $lapso->fecha_final ] )
                                ->sum('nom_doc_registros.valor_deduccion');
    }

    public function total_deducciones_modo_liquidacion_entidad_pension( $lapso, $modo_liquidacion_id, $entidad_pension_id )
    {
        $ids_empleados_entidad = NomContrato::where( 'entidad_pension_id', $entidad_pension_id )->get()->pluck('id')->toArray();

        return NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                ->where( 'nom_conceptos.modo_liquidacion_id', $modo_liquidacion_id )
                                ->whereIn( 'nom_doc_registros.nom_contrato_id', $ids_empleados_entidad )
                                ->whereBetween( 'nom_doc_registros.fecha', [ $lapso->fecha_inicial, $lapso->fecha_final ] )
                                ->sum('nom_doc_registros.valor_deduccion');
    }
    
}
