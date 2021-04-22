@extends('core.procesos.layout')

@section( 'titulo', 'Asignar pesos a Asignaturas para cálculo de Media Ponderada del área' )

@section('detalles')
	<p>
		Este proceso permite asignar el valor porcentual de pesos a las asignaturas de cada área para el cálculo de la Media Ponderada del área en las calificaiones finales. 
	</p>
	<br>
@endsection

@section('formulario')

	<div class="row">
		<div class="col-md-12">

			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" > <b> Año Lectivo: </b> </label>

				<div class="col-sm-8">
					{{ Form::select('periodo_lectivo_id', App\Matriculas\PeriodoLectivoAux::opciones_campo_select(), null, ['id' => 'periodo_lectivo_id', 'class' => 'form-control', 'required' => 'required' ] ) }}
				</div>
			</div>

		</div>
	</div>

	<div class="row">
		<div class="col-md-12">

			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" > <b> Grado: </b> </label>

				<div class="col-sm-8">
					{{ Form::select('grado_id', App\Matriculas\Grado::opciones_campo_select(), null, ['id' => 'grado_id', 'class' => 'form-control', 'required' => 'required' ] ) }}
				</div>
			</div>

		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<button class="btn btn-success" id="btn_cargar"> <i class="fa fa-arrow-up"></i> Cargar áreas</button>
		</div>
	</div>

	{{ Form::Spin(64) }}
	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#btn_cargar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
				$("#div_resultado").html('');

				var url = "{{url('/')}}" + '/sga_consultar_areas_asignaturas_pesos/';

				$.get( url + $('#periodo_lectivo_id').val() + '/' + $('#grado_id').val(), function(respuesta) {
					$('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
				});

		    });

		});
	</script>
@endsection