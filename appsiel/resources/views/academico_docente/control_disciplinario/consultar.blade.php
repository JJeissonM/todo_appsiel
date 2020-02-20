@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
<div class="container-fluid">
	<div class="marco_formulario">
	    <h4> Control académico y disciplinario </h4>
	    <hr>
	    	<div class="row">
				<div class="col-sm-2">
					<b>Curso: </b><code>{{ $curso->descripcion }}</code>
				</div>	
				<div class="col-sm-8">
					{{ Form::bsSelect('semana_id',$semana_actual->id.'a3p0'.$semana_actual->fecha_inicio,'Semana',$semanas,[]) }}

					{{ Form::hidden('curso_id', $curso->id, ['id' =>'curso_id']) }}

				</div>
				<div class="col-sm-2">
					<a href="#" class="btn btn-default btn-xs" id="btn_actualizar">Actualizar</a>
				</div>							
			</div>
			<div class="row">
				<div class="col-sm-12">
					<br>
					<div class="alert alert-info alert-dismissible">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					  Pase el mouse por encima de cada código de abajo para leer su descripción.
					</div>

					{{ Form::bsBtnExcel('control_disciplinario') }}
					{{ Form::bsBtnPdf('control_disciplinario') }}

					<br><br>

					{!! $tabla !!}
			</div>
		</div>	
	</div>			
</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('#semana_id').focus();

			var id = getParameterByName('id');

			// Asignar URL al boton de imprimir PDF
			$("#btn_excel").show();
			$("#btn_pdf").show();
			var url_pdf = $('#btn_pdf').attr('href');
			var n = url_pdf.search('a3p0');
			if ( n > 0) 
			{
				var fecha = $('#semana_id').val().split('a3p0');
				var new_url = url_pdf.replace('a3p0','matriculas/control_disciplinario/imprimir/' + $('#curso_id').val() + '/' + fecha[1] + '?id=' + id);
			}			
			
			$('#btn_pdf').attr('href', new_url);

			// Al cambiar de semana, se asigna una nueva URL al LINK del botón actualizar
			$('#semana_id').on('change',function()
			{
				var fecha = $('#semana_id').val().split('a3p0');

				$('#btn_actualizar').attr('href','../../../../matriculas/control_disciplinario/consultar/' + $('#curso_id').val() + '/' + fecha[1] + '?id=' + id );
				$('#btn_actualizar').focus();
			});

			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}
		});

	</script>
@endsection