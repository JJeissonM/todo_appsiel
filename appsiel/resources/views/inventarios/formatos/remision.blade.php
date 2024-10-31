<!DOCTYPE html>
<html>

<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <style>
        
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

        table.table-bordered,
        .table-bordered>tbody>tr>td {
            border: 1px solid #42A3DC;
        }

        thead>tr {
            background-color: #42A3DC !important;
            color: white !important;
            border-radius: 3px 3px 0 0;
        }

    .headempresa {
        border-radius: 20px 0 0 0px;
        width: 100%;
        height: 150px;
        border: 2px solid #42A3DC;
      }
      
      .headdoc {
        margin: 0px;
        border-radius: 0px 20px 0px 0px;
        width: 100%;
        height: 150px;
        border: 2px solid #42A3DC;
        border-left: 10px solid #42A3DC;
        background-color: whitesmoke;
        padding: 0 2rem;
      }

      .subhead{
        border: 2px solid #42A3DC;
        width: 100%;
        border-radius: 0 0 20px 20px;
        padding-left: 15px;
      }
    </style>
 <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
</head>

<body>
    <table class="table">
        <tr>
            <td style="/*border: solid 1px #ddd;*/ border: none;" width="60%" colspan="3">
                <div class="headempresa">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
                </div>
            </td>
            <td colspan="2">

                <div class="headdoc">
                    <br>
                    <b
                        style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                    <table style="margin-top: 30px;">
                        <tr>
                            <td>
                                <b>Documento:</b>
                            </td>
                            <td>
                                {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Fecha:</b>
                            </td>
                            <td>
                                {{ $doc_encabezado->fecha }}
                            </td>
                            <td>
                                @if($doc_encabezado->estado == 'Anulado')
                                <br><br>
                                <div style="background-color: #ddd;">
                                    <strong>Documento Anulado</strong>
                                </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>                
            </td>
        </tr>
        <div class="subhead">
            <table>
                <tr>
                    <td colspan="5">
                        <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                        <br />
                        <b>{{ config("configuracion.tipo_identificador") }}: </b>
                        @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}
                    </td>                
                </tr>
                <tr>
                    <td colspan="5">
                        
                    </td>
                </tr>
            </table>
        </div>
        
        
    </table>
    <br>
    <br>
    <table class="table table-bordered" >
        <thead>
            <tr style="text-align: center; background-color: #ddd; font-weight: bolder;">
                <td>No.</td>
                <td>Cód.</td>
                <td>Producto</td>
                <td>U.M.</td>
                <td>Cantidad</td>
            </tr>
        </thead>


        <?php
                $total_cantidad = 0;
                $numero = 1;
            ?>
        @foreach($doc_registros as $linea )
            <tr>
                <td style="text-align: center;"> {{ $numero }} </td>
                <td style="text-align: center;"> {{ $linea->producto_id }} </td>
                <td> {{ $linea->item->get_value_to_show(true) }} </td>
                <td style="text-align: center;"> {{ $linea->unidad_medida1 }} </td>
                <td style="text-align: right;"> {{ number_format( abs($linea->cantidad), 2, ',', '.') }} </td>
            </tr>
            <?php 
                $total_cantidad += $linea->cantidad;
                $numero++;
            
            ?>
        @endforeach
        <tr>
            <td colspan="4">&nbsp;</td>
            <td style="text-align: right;"> {{ number_format( abs($total_cantidad), 2, ',', '.') }} </td>
        </tr>
    </table>

    <br><br><br><br>

    <table border="0">
        <tr>
            <td width="50px"> &nbsp; </td>
            <td align="center" style="border-bottom: 1px solid;"> </td>
            <td align="center"> &nbsp; </td>
            <td align="center" style="border-bottom: 1px solid;"> </td>
            <td width="50px">&nbsp;</td>
        </tr>
        <tr>
            <td width="50px"> &nbsp; </td>
            <td align="center"> Elaboró: {{ explode('@',$doc_encabezado->creado_por)[0] }} </td>
            <td align="center"> &nbsp; </td>
            <td align="center"> Recibe </td>
            <td width="50px">&nbsp;</td>
        </tr>
    </table>
<br>
<b>Detalle: &nbsp;&nbsp;</b> {!! $doc_encabezado->descripcion !!}
</body>

</html>