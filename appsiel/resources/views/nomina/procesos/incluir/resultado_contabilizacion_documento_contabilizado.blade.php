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
		        	@if( $accion == 'retirar')
			        	<div class="alert alert-{{$clase}}">
						  <strong>{{ $mensaje }}</strong>
						</div>
					@else
						<div class="alert alert-warning">
						  <strong>Advertencia!</strong>
						  <br>
						   El documento de nómina YA se encuentra contabilizado.
						</div>
					@endif
		        </td>
		    </tr>
		</table>
	</div>
</div>