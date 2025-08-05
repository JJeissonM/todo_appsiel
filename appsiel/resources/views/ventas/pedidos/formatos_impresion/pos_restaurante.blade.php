<!DOCTYPE html>
<html>
<head>
    <title><div style="display: inline;" id="doc_encabezado_documento_transaccion_prefijo_consecutivo"></div></title>
    <!-- <link rel="stylesheet" href="{ { asset("css/stylepdf.css") }}"> -->
    <style type="text/css">
        
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ config('ventas_pos.tamanio_fuente_factura') . 'px'  }};
        }

        @page {
          margin: 15px;
          size: {{ config('ventas_pos.ancho_formato_impresion') . 'in' }} 38.5in;
        }

        .lbl_doc_anulado{
            position: absolute;

            background-color: rgba(253, 1, 1, 0.33);
            width: 100%;
            top: 100px;
            transform: rotate(-45deg);
            text-align: center;
            font-size: 2em;
        }

        table {
            width:100%;
            border-collapse: collapse;
        }

        .table
		    {
			    width: 100%;
			}


		    .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th
		    {
			    line-height: 1.42857143;
			    vertical-align: top;
			    border-top: 1px solid gray;
			}


		    .table-bordered {
			    border: 1px solid gray;
			}

			.table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
			    border: 1px solid gray;
			}
    </style>
</head>
<body>
    <?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
    ?>
    <table border="0" style="margin-top: 0px !important;" width="100%">
        <tr>
            <td>
                <div class="headempresap" style="text-align: center;">
                    <br/>
                    <b>{{ $empresa->descripcion }}</b><br/>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 15px;">
                <div class="headdocp" style="text-align: center;">
                    <b><div style="display: inline;" id="doc_encabezado_documento_transaccion_descripcion" ></div> 
                    <br>
                    No.</b> <div style="display: inline;" id="doc_encabezado_documento_transaccion_prefijo_consecutivo"></div>
                    <br>
                    <b>Fecha:</b> <div style="display: inline;" id="doc_encabezado_fecha"></div>
                    &nbsp;&nbsp;&nbsp;
                    <b>Hora:</b> <div style="display: inline;" id="doc_encabezado_hora_creacion"></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="subheadp" >
        <b>Cliente:</b> <div style="display: inline;" id="doc_encabezado_tercero_nombre_completo"></div> 
        <br>
        <b>Atendido por: &nbsp;&nbsp;</b> 
        <div style="display: inline;" id="doc_encabezado_vendedor_descripcion" ></div>
        <br>
    </div>

    <table class="table table-bordered;" style="width: 100%; font-size: 13px;" id="tabla_productos_facturados">
        {{ Form::bsTableHeader(['Producto','Cant. pedida','Despachada']) }}
        <tbody>
        </tbody>
    </table>
    <br>
    <b> Cantidad de items&nbsp;: </b> <div style="display: inline;" id="cantidad_total_productos" ></div>
    <br>
    <b> Despachado por &nbsp;&nbsp;&nbsp;: </b> _____________________    
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> <div style="display: inline;" id="doc_encabezado_descripcion"></div>
    <br><br>
    
    <script type="text/javascript">
        window.onkeydown = function( event ) {
            // Si se presiona la tecla q (Quit)
            if ( event.keyCode == 81 )
            {
                window.close();
            }
        };
    </script>

</body>

</html>