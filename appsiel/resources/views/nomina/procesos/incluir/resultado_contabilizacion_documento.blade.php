<br><br>
<div class="marco_formulario">
	<div class="container-fluid">
		
		<h4 style="width: 100%;text-align: center;">
			<strong>
				Datos de contabilización del documento de nómina
			</strong>
		</h4>
		
		<table class="table table-bordered">
		    <tr>
		        @php 
		            $fecha = explode("-",$encabezado_doc->fecha) 
		        @endphp
		        <td>
		        	<b>Fecha:</b>
			        {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
			    </td>
		        <td>
		        	<b>Documento:</b>
		        	{{ $encabezado_doc->tipo_documento_app->prefijo }} {{ $encabezado_doc->consecutivo }}
		        </td>
		        <td>
		        	<b>Detalle: </b>
		        	{{ $encabezado_doc->descripcion }}
		        </td>
		    </tr>
		    <tr>
		        <td>
		            <b>Total Devengos: </b>
		             &nbsp; ${{ number_format( $encabezado_doc->total_devengos, '0','.',',') }}
		        </td>
		        <td>
		            <b>Total Deducciones: </b>
		             &nbsp; ${{ number_format( $encabezado_doc->total_deducciones, '0','.',',') }}
		        </td>
		        <td>
		            <b>Valor Neto: </b> 
		            &nbsp; ${{ number_format( $encabezado_doc->total_devengos - $encabezado_doc->total_deducciones, '0','.',',') }}
		        </td>
		    </tr>
		    <tr>
		        <td colspan="3">
		            <span style="color:red;">
		            	<b>Nota: </b>
		            	Las líneas sombreadas de rojo no serán contabilizadas.
		         	</span>
		        </td>
		    </tr>
		</table>

		@if( $contabilizado )
			<div class="alert alert-success">
			  <strong>Documento contabilizado correctamente.</strong> 
				<a href="{{url('nomina/' . $encabezado_doc->id . '?id=17&id_modelo=90&id_transaccion=14')}}" target="_blank" class="btn btn-info btn-sm"><i class="fa fa-external-link"></i> Consultar </a>
			</div>
		@else
			<div class="alert alert-info">
			  <strong>Previsualización.</strong>
			</div>
		@endif

		<table class="table table-striped">
			<thead>
				<tr>
					<th>Tipo causación</th>
					<th>Cuenta contable</th>
					<th>Tercero movimiento</th>
					<th>Concepto</th>
					<th>Débito</th>
					<th>Crédito</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$valor_debito_total = 0;
					$valor_credito_total = 0;
				?>
				@foreach( $lineas_tabla AS $linea )
					<?php 
						$clase = '';
						if( $linea->error )
						{
							$clase = 'danger';
						}
					?>
					<tr class="{{$clase}}">
						<td> {{ $linea->tipo_causacion }} </td>
						<td> {{ $linea->cuenta_contable }} </td>
						<td> {{ $linea->tercero_movimiento }} </td>
						<td> {{ $linea->concepto }} </td>
						<td> ${{ number_format($linea->valor_debito,0,',','.') }} </td>
						<td> ${{ number_format($linea->valor_credito,0,',','.') }} </td>
						<td> {!! $linea->observacion !!} </td>
					</tr>
					<?php 
						$valor_debito_total += $linea->valor_debito;
						$valor_credito_total += $linea->valor_credito;
					?>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4"></td>
					<td> ${{ number_format($valor_debito_total,0,',','.') }} </td>
					<td> ${{ number_format($valor_credito_total,0,',','.') }} </td>
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>