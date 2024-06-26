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
		        	@if( $accion == 'retirar')
			        	<div class="alert alert-{{$clase}}">
						  <strong>{{ $mensaje }}</strong>
						</div>
					@else
						<div class="alert alert-warning">
						  <strong>Advertencia!</strong>
						  <br>
						   Los registros de planilla integrada para el mes seleccionado, YA se encuentran contabilizados con el documento {{ $encabezado_doc->tipo_documento_app->prefijo }} {{ $encabezado_doc->consecutivo }}.
						   <a href="{{url( 'contabilidad/' . $encabezado_doc->id . '?id=14&id_modelo=47&id_transaccion=9')}}" target="_blank" class="btn btn-info btn-sm"><i class="fa fa-external-link"></i> Consultar </a>
						</div>
					@endif
		        </td>
		    </tr>
		</table>
	</div>
</div>