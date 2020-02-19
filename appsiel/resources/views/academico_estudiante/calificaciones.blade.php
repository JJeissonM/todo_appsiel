@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			
			<h3>{{ $estudiante->nombre_completo }}</h3>
			<h4>Matrícula: {{ $codigo_matricula }} /  Curso: {{ $curso->descripcion }}</h4>

			{{ Form::open(['url'=>'academico_estudiante/ajax_calificaciones','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-2">
						{{ Form::label('periodo_id','Seleccionar periodo') }}
						{{ Form::select('periodo_id',$periodos,null,['class'=>'form-control','id'=>'periodo_id']) }}
					</div>
					<div class="col-sm-2">
					</div>
					<div class="col-sm-3">
					</div>
					<div class="col-sm-3">
					</div>
					<div class="col-sm-2">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Consultar</a>
					</div>
				</div>

				{{ Form::hidden('curso_id',$curso->id) }}
				
			{{ Form::close() }}
			<!--	<button id="btn_ir">ir</button>	-->
			
		</div>
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('calificaciones') }}

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('[data-toggle="popover"]').popover();

			$('[data-toggle="tooltip"]').tooltip();
			
			$('#periodo_id').focus();

			$('#periodo_id').change(function(){
				$('#btn_generar').focus();
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos())
				{
					alert('Debe ingresar el periodo.');
					$('#periodo_id').focus();
					return false;
				}

				$('#div_cargando').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#resultado_consulta').html(respuesta);
					$('#btn_excel').show(500);
				});
			});

			function valida_campos(){
				var valida = true;
				if( $('#periodo_id').val() == '' ){
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection