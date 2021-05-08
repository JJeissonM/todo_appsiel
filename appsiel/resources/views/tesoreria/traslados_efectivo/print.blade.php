<!DOCTYPE html>
<html>
<head>
    <title>Traslado de Efectivo</title>
    <style>
        .marco_formulario h4 {
            color: gray;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table.table-bordered, .table-bordered > tbody > tr > td {
            border: 1px solid #ddd;
        }

        .container-fluid {
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }

        .marco_formulario {
            background-color: white;
            border: 1px solid #d9d7d7;
            box-shadow: 5px 5px grey;
            font-size: 13px;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .container {
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }

        .h4, h4 {
            font-size: 18px;
        }

        h1 {
            font-size: 2em;
            margin: 0.67em 0;
        }

        .row {
            margin-right: -15px;
            margin-left: -15px;
        }

        .table-bordered {
            border: 1px solid #ddd;
        }

        .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 {
            position: relative;
            min-height: 1px;
            padding-right: 15px;
            padding-left: 15px;
        }

        .table {
            width: 100%;
            max-width: 100%;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        table {
            background-color: transparent;
        }

        tbody {
            display: table-row-group;
            vertical-align: middle;
            border-color: inherit;
        }

        tr {
            display: table-row;
            vertical-align: inherit;
            border-color: inherit;
        }

        .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th {
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border-top: 1px solid #ddd;
        }

        td {
            display: table-cell;
            vertical-align: inherit;
        }

        blockquote {
            padding: 10px 20px;
            margin: 0 0 20px;
            font-size: 17.5px;
            border-left: 5px solid #eee;
        }
        
    </style>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
</head>
<body>
<div class="col-md-12">
    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="container">
                <table>
                    <tr>
                        <td width="50%" >
                            <div class="headempresa">
                                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
                            </div>                            
                        </td>
                        <td>
                            <div class="headdoc"> 
                                <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                                <br/>
                                <b>Documento:</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                                <br/>
                                <b>Fecha:</b> {{ $doc_encabezado->fecha }}

                                @yield('datos_adicionales_encabezado')

                            </div>
                        </td>
                    </tr>
                </table>
                <div class="subhead">
                    <table>
                        <tr>
                            <td>
                                <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                                <br/>
                                <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
                                <br/>
                                <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
                                <br/>
                                <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
                            </td>
                            <td>
                                @if($doc_encabezado->estado == 'Anulado')
                                    <b>Documento Anulado</b>
                                    @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                
                            </td>
                        </tr>
                    </table>    
                </div>
                
                <hr>
                <?php
                $total_recaudo = 0;
                $i = 0;
                $vec_motivos;
                ?>
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;">
                            Detalles del traslado
                        </div>
                        <table class="table table-bordered">
                            {{ Form::bsTableHeader(['Medio de pago','Motivo','Caja/Cta. Bancaria','Valor']) }}
                            <tbody>
                            @foreach ($doc_registros as $registro)
                                <tr>
                                    <td> {{ $registro->medio_recaudo }} </td>
                                    <td> {{ $registro->motivo }} </td>
                                    <td> {{ $registro->caja }} {{ $registro->cuenta_bancaria }} </td>
                                    <td> ${{ number_format($registro->valor, 0, ',', '.') }} </td>
                                </tr>
                                <?php
                                $total_recaudo += $registro->valor;
                                ?>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td> &nbsp; </td>
                                <td> &nbsp; </td>
                                <td>   </td>
                                <td>
                                    ${{ number_format($total_recaudo, 0, ',', '.') }} ({{ NumerosEnLetras::convertir($total_recaudo,'pesos',false) }})
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <blockquote>Formato generado por Appsiel</blockquote>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<b>Detalles:&nbsp;&nbsp;</b>{{ $doc_encabezado->descripcion }}
</body>
</html>