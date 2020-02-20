@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	@if(isset($url_crear))
		@if( $url_crear != '' )		
			&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( $url_crear ) }}
		@endif
	@endif
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">

		<div class="table-responsive">
			<table class="table table-bordered table-striped" id="myTable">
				{{ Form::bsTableHeader($encabezado_tabla) }}
				<tbody>

					@foreach ($registros as $fila)
						<tr>
							
								@foreach( $fila as $key => $value )
									
									@if($key == 'accion')
									<td>
									@if(isset($url_ver))
										@if($url_ver!='')
											{{ Form::bsBtnVer(str_replace('id_fila', $value, $url_ver)) }}
										@endif
									@endif
									@if(isset($url_print))
										@if($url_print!='')
											&nbsp;
											{{ Form::bsBtnPrint(str_replace('id_fila', $value, $url_print)) }}
										@endif
									@endif
									@if(isset($url_estado))
										@if($url_estado!='')
											&nbsp;
											{{ Form::bsBtnEstado(str_replace('id_fila', $value, $url_estado)) }}
										@endif
									@endif
									@if(isset($url_edit))
										@if($url_edit!='')
											&nbsp;
											{{ Form::bsBtnEdit(str_replace('id_fila', $value, $url_edit)) }}
										@endif
									@endif

									@if(isset($url_eliminar))
										@if($url_eliminar!='')
											&nbsp;&nbsp;&nbsp;
											{{ Form::bsBtnEliminar(str_replace('id_fila', $value, $url_eliminar)) }}
										@endif
									@endif

									@if(isset($botones))
										@foreach($botones as $boton)
											{!! str_replace( 'id_fila', $value, $boton->dibujar() ) !!}
										@endforeach
									@endif
									
								</td>
									@else
										<td class="table-text">
											{{ $value }}
										</td>
									@endif
								
						

								@endforeach
					
								

						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@endsection