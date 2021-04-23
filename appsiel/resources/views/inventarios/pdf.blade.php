<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">

	    <title>APPSIEL ..:: Sistemas para el crecimiento empresarial ::..</title>

	    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
		<link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
	    <style>
			.headempresa{
				height: 100px;	
				font-size: 16px;
				padding-top: 24px;			
			}
			.tit{
				text-align: center;
			}
			.headdoc{
				height: 100px;
				font-size: 12px;
				padding-top: 24px;
			}
	    </style>

	</head>
	<body id="app-layout">

		<table class="table tabla_pdf">
		    <tr>
		        <td>
					<div class="headempresa">
						<h2 class="tit">{{ $descripcion_transaccion }}</h2>				
					</div>					
		        </td>
		        <td>
		            <div class="headdoc">
		                <br/>
		                <b>Documento:</b> {{ $datos_encabezado_doc['campo2'] }}
		                <br/>
		                <b>Fecha:</b> {{ $datos_encabezado_doc['campo1'] }}
		                <?php 
		                    $reg_fatura_venta = App\Ventas\VtasDocEncabezado::where('remision_doc_encabezado_id',$datos_encabezado_doc['campo9'])->get()->first();

		                    if( !is_null($reg_fatura_venta) )
		                    {
		                        $fatura_venta = App\Ventas\VtasDocEncabezado::get_registro_impresion( $reg_fatura_venta->id );
		                        echo '<br/>
		                                <b>Factura de ventas: </b> <a href="'.url('ventas/'.$fatura_venta->id.'?id=13&id_modelo=139').'" target="_blank">'.$fatura_venta->documento_transaccion_prefijo_consecutivo.'</a>';
		                    }
		                ?>
		                <?php 
		                    $reg_fatura_compras = App\Compras\ComprasDocEncabezado::where('entrada_almacen_id',$datos_encabezado_doc['campo9'])->get()->first();

		                    if( !is_null($reg_fatura_compras) )
		                    {
		                        $fatura_compra = App\Compras\ComprasDocEncabezado::get_registro_impresion( $reg_fatura_compras->id );
		                        echo '<br/>
		                                <b>Factura de compras: </b> <a href="'.url('compras/'.$fatura_compra->id.'?id=9&id_modelo=147').'" target="_blank">'.$fatura_compra->documento_transaccion_prefijo_consecutivo.'</a>';
		                    }
		                ?>
		            </div>
		        </td>
		    </tr>
		   
		</table>
		<div class="subhead">
			<table>
				<tr>
					<td>
						<b>Bodega:</b> {{ $datos_encabezado_doc['campo7'] }}
					</td>
					<td>
						<b>Tercero:</b> {{ $datos_encabezado_doc['campo3'] }}
					</td>
				</tr>
				<tr>
					<td>
						
					</td>
					<td>
						<b>Doc. soporte:</b> {{ $datos_encabezado_doc['campo5'] }}
					</td>
				</tr>
			</table>	
		</div>
		<br>
		

		<table class="table table-bordered tabla_pdf">
		    <thead>
		        <tr>
		            <th>Cód.</th>
		            <th>Producto</th>
		            <th>Bodega</th>
		            <th>Costo Unit.</th>
		            <th>Cantidad</th>
		            <th>Costo Total</th>
		        </tr>
		    </thead>
		    <tbody>
		        <?php 
		        $total_cantidad=0;
		        $total_costo_total=0;
		        $cantidad = count($productos);
		        for($i=0; $i < $cantidad; $i++)
		        { ?>
		            <tr>
		                <td>{{ $productos[$i]['producto']->id}}</td>
		                <td>{{ $productos[$i]['producto']->descripcion}}</td>
		                <td>{{ $productos[$i]['bodega']}}</td>
		                <td>{{ '$'.number_format($productos[$i]['costo_unitario'], 2, ',', '.') }}</td>
		                <td>{{ number_format($productos[$i]['cantidad'], 2, ',', '.') }} {{ $productos[$i]['producto']->unidad_medida1 }}</td>
		                <td>{{ '$'.number_format($productos[$i]['costo_total'], 2, ',', '.') }}</td>
		            </tr>
		        <?php 
		            $total_cantidad+= $productos[$i]['cantidad'];
		            $total_costo_total+= $productos[$i]['costo_total'];
		        } ?>
		    </tbody>
		    <tfoot>
		        <tr>
		            <td colspan="4">&nbsp;</td>
		            <td> {{ number_format($total_cantidad, 0, ',', '.') }} </td>
		            <td> {{ '$'.number_format($total_costo_total, 0, ',', '.') }} </td>
		        </tr>
		    </tfoot>
		</table>

		<table>
		    <tr>
		        <td width="15%"> </td>
		        <td width="30%"> _______________________ </td>
		        <td width="10%"> </td>
		        <td width="30%"> _______________________ </td>
		        <td width="15%"> </td>
		    </tr>
		    <tr>
		        <td width="15%"> </td>
		        <td width="30%"> Elaboró: {{ explode("@",$elaboro)[0] }} </td>
		        <td width="10%"> </td>
		        <td width="30%"> Recibió </td>
		        <td width="15%"> </td>
		    </tr>
			
		</table>
		<b>Detalle:</b> {{ $datos_encabezado_doc['campo6'] }}
	</body>
</html>
