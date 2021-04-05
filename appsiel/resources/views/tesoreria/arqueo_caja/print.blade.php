<!DOCTYPE html>
<html>
<head>
    <title>Arqueo de Caja</title>
    
    <style>
        .marco_formulario h4 {
            color: gray;
        }

        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        table.table-bordered, .table-bordered>tbody>tr>td{
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
    <link rel="stylesheet" href="{{ url("css/stylepdf.css") }}">
</head>
<body>
<div class="col-md-12">
    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="container">
                <table class="table">
                    <tr>
                        <td width="50%">
                            <div class="headempresa">
                                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
                            </div>                            
                        </td>
                        <td>
                            <div class="headdoc">
                                <b style="font-size: 1.2em; text-align: center; display: block;">{{ $doc_encabezado['titulo'] }}</b>
                                <br/>
                                <b>Fecha:</b> {{ $doc_encabezado['fecha'] }}

                                @yield('datos_adicionales_encabezado')

                            </div>
                        </td>
                    </tr>
                   
                </table>
                <div class="subhead">
                    <table>
                        <tr>
                            <td>
                                <b>Responsable:</b> {{$user->name}}
                                <br/>
                                <b>Fecha y Hora de Realización: &nbsp;&nbsp;</b> {{$registro->created_at}}
                                <br/>
                            </td>
                            <td>
                                <b>Observaciones:</b> {{$registro->observaciones}}
                                <br/>
                            </td>                                               
                        </tr>
                        <tr>
                            <td colspan="2">
                                <b>Base: &nbsp;&nbsp;</b> ${{number_format($registro->base,'0',',','.')}}
                            </td>
                        </tr>
                    </table>    
                </div>
                
                <hr>
                <h1 class="card-inside-title">Datos de la fecha {{$registro->fecha}}</h1>
                <div class="row clearfix">
                    <div class="col-md-12">
                        <table class="table table-hover">
                            <tbody>
                            <tr>
                                <td colspan="3">
                                    <center><strong>ACTA DE ARQUEO DE CAJA</strong></center>
                                </td>
                            </tr>
                            <tr class="read">
                                <td class="contact"><b>EFECTIVO</b></td>
                                <td class="subject"><b>UNIDADES</b></td>
                                <td class="subject"><b>VALOR</b></td>
                            </tr>
                            @foreach($registro->billetes_contados as $key => $value)
                                <tr class="read">
                                    <td class="contact"><b>Billetes de ${{number_format($key,'0',',','.')}}</b></td>
                                    <td class="subject">{{$value == ""?0:$value}}</td>
                                    <td class="subject">
                                        ${{number_format($value == ""?0:$key*$value,'0',',','.')}}</td>
                                </tr>
                            @endforeach
                            @foreach($registro->monedas_contadas as $key => $value)
                                <tr class="read">
                                    <td class="contact"><b>Monedas de ${{number_format($key,'0',',','.')}}</b></td>
                                    <td class="subject">{{$value == ""?0:$value}}</td>
                                    <td class="subject">
                                        ${{number_format($value == ""?0:$key*$value,'0',',','.')}}</td>
                                </tr>
                            @endforeach
                            <tr class="read">
                                <td class="contact"><b>Otros Saldos (bonos,pagarés,etc.)</b></td>
                                <td class="subject">{{$registro->detalle_otros_saldos}}</td>
                                <td class="subject">${{number_format($registro->otros_saldos,'0',',','.')}}</td>
                            </tr>
                            <tr class="read">
                                <td class="contact"><b>Total Billetes</b></td>
                                <td class="subject"></td>
                                <td class="subject">${{number_format($registro->total_billetes,'0',',','.')}}</td>
                            </tr>
                            <tr class="read">
                                <td class="contact"><b>Total Monedas</b></td>
                                <td class="subject"></td>
                                <td class="subject">${{number_format($registro->total_monedas,'0',',','.')}}</td>
                            </tr>
                            <tr class="read">
                                <td class="contact"><b>Total Efectivo</b></td>
                                <td class="subject"></td>
                                <td class="subject">
                                    ${{number_format($registro->lbl_total_efectivo,'0',',','.')}}</td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <center><strong>MOVIMIENTOS DEL SISTEMA</strong></center>
                                </td>
                            </tr>
                            <tr class="read">
                                <td class="contact"><b>MOTIVO</b></td>
                                <td class="subject"><b>MOVIMIENTO</b></td>
                                <td class="subject"><b>VALOR</b></td>
                            </tr>
                            @foreach($registro->detalles_mov_entradas as $item)
                                <tr class="read">
                                    <td class="contact"><b>{{$item->motivo}}</b></td>
                                    <td class="subject">{{strtoupper($item->movimiento)}}</td>
                                    <td class="subject">${{number_format($item->valor_movimiento,'0',',','.')}}</td>
                                </tr>
                            @endforeach
                            @foreach($registro->detalles_mov_salidas as $item)
                                <tr class="read">
                                    <td class="contact"><b>{{$item->motivo}}</b></td>
                                    <td class="subject">{{strtoupper($item->movimiento)}}</td>
                                    <td class="subject">${{number_format($item->valor_movimiento,'0',',','.')}}</td>
                                </tr>
                            @endforeach
                            <tr class="read">
                                <td class="contact"><b>Total Entrada de Caja</b></td>
                                <td class="subject"></td>
                                <td class="subject">
                                    ${{number_format($registro->total_mov_entradas,'0',',','.')}}</td>
                            </tr>
                            <tr class="read">
                                <td class="contact"><b>Total Salida de Caja</b></td>
                                <td class="subject"></td>
                                <td class="subject">
                                    ${{number_format($registro->total_mov_salidas,'0',',','.')}}</td>
                            </tr>
                            <tr class="read">
                                <td class="contact"><b>Total Saldo en el Sistema</b></td>
                                <td class="subject"></td>
                                <td class="subject">
                                    ${{number_format($registro->lbl_total_sistema,'0',',','.')}}</td>
                            </tr>
                            <tr class="read">
                                <td class="contact"><b>Diferencia</b></td>
                                <td class="subject"></td>
                                <td class="subject">
                                    ${{number_format($registro->total_saldo,'0',',','.')}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <blockquote>Formato generado por Appsiel</blockquote>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>