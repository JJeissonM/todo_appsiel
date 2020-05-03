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
	<div class="table-responsive" id="table_content">
		<table class="table table-bordered table-striped" id="myTable">
			{{ Form::bsTableHeader($encabezado_tabla) }}
			<tbody>

				@foreach ($registros as $fila)
					<tr>
						<?php for($i=1;$i<count($fila);$i++){ ?>
							<td class="table-text">
								{{ $fila['campo'.$i] }}
							</td>
						<?php } ?>
							<td>
								@if(isset($url_ver))
									@if($url_ver!='')
										{{ Form::bsBtnVer(str_replace('id_fila', $fila['campo'.$i], $url_ver)) }}
									@endif
								@endif
								@if(isset($url_print))
									@if($url_print!='')
										&nbsp;
										{{ Form::bsBtnPrint(str_replace('id_fila', $fila['campo'.$i], $url_print)) }}
									@endif
								@endif
								@if(isset($url_estado))
									@if($url_estado!='')
										&nbsp;
										{{ Form::bsBtnEstado(str_replace('id_fila', $fila['campo'.$i], $url_estado)) }}
									@endif
								@endif
								@if(isset($url_edit))
									@if($url_edit!='')
										&nbsp;
										{{ Form::bsBtnEdit(str_replace('id_fila', $fila['campo'.$i], $url_edit)) }}
									@endif
								@endif

								@if(isset($url_eliminar))
									@if($url_eliminar!='')
										&nbsp;&nbsp;&nbsp;
										{{ Form::bsBtnEliminar(str_replace('id_fila', $fila['campo'.$i], $url_eliminar)) }}
									@endif
								@endif

								@if(isset($botones))
									@foreach($botones as $boton)
										{!! str_replace( 'id_fila', $fila['campo'.$i], $boton->dibujar() ) !!}
									@endforeach
								@endif
							</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){

			$('input').focus( );			

		});
	</script>

	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif

@endsection

