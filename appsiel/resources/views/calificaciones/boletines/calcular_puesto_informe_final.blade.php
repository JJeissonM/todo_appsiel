@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-md-8 col-md-offset-2 marco_formulario">
		    <h4>Calcular puesto para boletines de estudiantes</h4>
		    <hr>

		    {{ Form::open( [ 'url' => 'calificaciones/boletines/calcular_puesto_informe_final', 'method' => 'POST' , 'id' => 'mi_formulario'] ) }}

    			<div class="row" style="padding:5px;">
					{{ Form::bsCheckBox('periodos_promediar','','Periodos a promediar',$periodos,['required' => 'required']) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('id_periodo','','Periodo observaciones y logros',$periodos,['required' => 'required']) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('curso_id','','Seleccionar curso',$cursos,['required' => 'required']) }}
				</div>

				<div style="padding:5px;" align="center">
					<button class="btn btn-primary btn-sm" id="btn_continuar">
						<i class="fa fa-btn fa-calculator"></i> Calcular
					</button>
				</div>

				{{ Form::hidden('id_app',Input::get('id')) }}

			{{Form::close()}}
		
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#id_periodo').focus();

			$("#id_periodo").on('change',function(){
				$(".alert").alert("close");
				$('#curso_id').focus();
			});

			$("#curso_id").on('change',function(){
				$('#btn_continuar').focus();
			});

			/*  Falta agregar los mensajes de respuestas
			$("#btn_continuar").on('clic',function(){
				var action = $("#mi_formulario").action();
				var datos = $("#mi_formulario").serialize();
				
				$.post( action, function( data ) {
					  $( ".result" ).html( data );
				});
			});
			*/
			
		});
	</script>
@endsection