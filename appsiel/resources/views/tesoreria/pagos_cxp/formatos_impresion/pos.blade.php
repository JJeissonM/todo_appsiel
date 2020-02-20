@extends('transaccion.formatos_impresion.pos')

@section('documento_transaccion_prefijo_consecutivo')
    {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
@endsection

@section('lbl_tercero')
    Tercero:
@endsection

@section('encabezado_datos_adicionales')
    <br/>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
@endsection

@section('tabla_registros_1')
    <div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Documentos pagados </div>
    <table class="table table-bordered table-striped" style="width: 100%;">
        {{ Form::bsTableHeader(['Tercero','Doc.','Fecha','Abono']) }}
        </thead>
        <tbody>
            <?php 
            
            $total_abono = 0;

            ?>
            @foreach($doc_pagados as $linea )

                <?php 
            
                    $el_documento = app( App\Sistema\TipoTransaccion::find( $linea->doc_cxp_transacc_id )->modelo_encabezados_documentos )->where('core_tipo_transaccion_id',$linea->doc_cxp_transacc_id)
                    ->where('core_tipo_doc_app_id',$linea->doc_cxp_tipo_doc_id)
                    ->where('consecutivo',$linea->doc_cxp_consecutivo)
                    ->get()->first();

                ?>

                <tr>
                    <td> {{ $linea->tercero_nombre_completo }} </td>
                    <td> {{ $linea->documento_prefijo_consecutivo }} </td>
                    <td> {{ $el_documento->fecha }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->abono, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_abono += $linea->abono;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">&nbsp;</td>
                <td style="text-align: right;"> ${{ number_format($total_abono, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
@endsection

@section('tabla_registros_3')
    <br>
    @include('contabilidad.incluir.firmas',['elaboro'=>$doc_encabezado->creado_por])
@endsection
