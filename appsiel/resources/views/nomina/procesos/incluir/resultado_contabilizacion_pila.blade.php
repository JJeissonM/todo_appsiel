<br><br>
<div class="marco_formulario">
	<div class="container-fluid">
		
		<h4 style="width: 100%;text-align: center;">
			<strong>
				Datos de contabilización planilla integrada
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
		        <td colspan="3">
		            <span style="color:red;">
		            	<b>Nota: </b>
		            	Las líneas sombreadas de rojo no serán contabilizadas.
		         	</span>
		            <br>
		            <span class="text-info">
		                <b>Conciliación PILA: </b>
		                El valor PILA corresponde al total almacenado en las tablas de liquidación. El valor contable puede ser menor en EPS y AFP porque allí solo se causa el aporte patronal pendiente; las deducciones del trabajador deben venir causadas desde la nómina.
		            </span>
		        </td>
		    </tr>
		</table>

		@if( $contabilizado )
			<div class="alert alert-success">
			  <strong>Registros de Planilla integrada contabilizados correctamente.</strong>
			</div>
		@else
			<div class="alert alert-info">
			  <strong>Previsualización.</strong>
			</div>
		@endif

		<table class="table table-striped">
			<thead>
				<tr>
		    		<th>No.</th>
					<th>Tipo causación</th>
					<th>Cuenta contable</th>
					<th>Tercero movimiento</th>
					<th>Concepto</th>
					<th>Valor PILA</th>
					<th>Débito</th>
					<th>Crédito</th>
					<th>Detalle</th>
					<th>Observación</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$valor_pila_total = 0;
					$valor_debito_total = 0;
					$valor_credito_total = 0;
					$i = 1;
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
				        <td> {{ $i }} </td>
						<td> {{ $linea->tipo_causacion }} </td>
						<td> {{ $linea->cuenta_contable }} </td>
						<td> {{ $linea->tercero_movimiento }} </td>
						<td> {{ $linea->concepto }} </td>
						<td> ${{ number_format($linea->valor_pila,0,',','.') }} </td>
						<td> ${{ number_format($linea->valor_debito,0,',','.') }} </td>
						<td> ${{ number_format($linea->valor_credito,0,',','.') }} </td>
						<td> {{ $linea->detalle_calculo_contable }} </td>
						<td> {!! $linea->observacion !!} </td>
					</tr>
					<?php 
						$valor_pila_total += $linea->valor_pila;
						$valor_debito_total += $linea->valor_debito;
						$valor_credito_total += $linea->valor_credito;
						$i++;
					?>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5"></td>
					<td> ${{ number_format($valor_pila_total,0,',','.') }} </td>
					<td> ${{ number_format($valor_debito_total,0,',','.') }} </td>
					<td> ${{ number_format($valor_credito_total,0,',','.') }} </td>
					<td></td>
					<td></td>
				</tr>
			</tfoot>
		</table>

		@if( $valor_debito_total == 0 )
			<div class="alert alert-warning">
			  <strong>No se ha procesado la planilla integrada en el mes seleccionado.</strong>
			</div>
		@endif
	</div>
</div>
