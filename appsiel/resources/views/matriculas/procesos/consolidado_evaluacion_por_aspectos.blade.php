@extends('core.procesos.layout')

@section( 'titulo', 'Consolidado de observación Académica-Comportamental' )

@section('seccion_encabezado')

	<a class="btn btn-info btn-md" href="{{url('/index_procesos/matriculas.procesos.listado_congratulations?id=' . Input::get('id') )}}" title="Listado de Congratulations"><i class="fa fa-list"></i> Listado de Congratulations </a>
	<a class="btn btn-warning btn-md" href="{{url('/index_procesos/matriculas.procesos.generar_estadisticas_evaluacion_aspectos_por_curso?id=' . Input::get('id') )}}" title="Estadísticas por curso"><i class="fa fa-pie-chart"></i> Estadísticas por curso </a>

	<br><br>

@endsection

@section('detalles')
	<p>
		Este proceso consolida todas las valoraciones realizadas por cada asignatura en la distintas fechas y determina la escala de frecuencia obtenida por cada estudiante. 
	</p>
	<p class="text-info">
		Nota: El sistema tomará las últimas tres valoraciones ingresadas dentro del rango de fechas.
	</p>
	<br>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">

		<div class="row">
			<div class="col-md-12">

				<div class="marco_formulario">
					<div class="container-fluid">
						<h4>
							Parámetros de selección
						</h4>
						<hr>
						{{ Form::open(['url'=>'sga_observador_evaluacion_por_aspectos_consolidar','id'=>'formulario_inicial']) }}
							<div class="row">
								<div class="col-sm-4">
									{{ Form::label('semana_calendario_id','Semanas de evaluacion') }}
									<br/>
									{{ Form::select('semana_calendario_id',\App\Core\SemanasCalendario::opciones_campo_select(),null,[ 'class' => 'form-control', 'id' => 'semana_calendario_id', 'required' => 'required' ]) }}
								</div>
								<div class="col-sm-3">
									{{ Form::label('curso_id','Curso') }}
									<br/>
									{{ Form::select('curso_id',\App\Matriculas\Curso::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'curso_id', 'required' => 'required' ]) }}
								</div>
								<div class="col-sm-3">
									{{ Form::label('asignatura_id','Asignatura') }}
									<br/>
									{{ Form::select('asignatura_id',[],null, [ 'class' => 'form-control', 'id' => 'asignatura_id', 'required' => 'required' ]) }}
								</div>
								<div class="col-sm-2">
									{{ Form::label(' ','.') }}
									<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
								</div>
							</div>
						{{ Form::close() }}
					</div>
						
				</div>
					
			</div>
		</div>
				
	</div>

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('#curso_id').on('change',function()
			{
				// Debe haber Select Asignatura
				$('#asignatura_id').html('<option value=""></option>');

				if ( $(this).val() == '') { return false; }

	    		$('#div_cargando').show();

				var url = "{{ url('calificaciones_opciones_select_asignaturas_del_curso') }}" + "/" + $('#curso_id').val() + "/null" + "/null" + "/Activo";

				$.ajax({
		        	url: url,
		        	type: 'get',
		        	success: function(datos){

		        		$('#div_cargando').hide();
	    				
	    				$('#asignatura_id').html( datos );
						$('#asignatura_id').focus();
			        }
			    });
					
			});

			$("#btn_generar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		    	$('#div_form_cambiar').hide();

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
				$("#div_resultado").html('');

				var form = $('#formulario_inicial');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("formulario_inicial"));

				$.ajax({
				    url: url,
				    type: "post",
				    dataType: "html",
				    data: datos,
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
        			$("#checkbox_head").focus();
			    });
		    });

			$(document).on('hover','td',function(){
				var fila_encabezado  = $('table thead tr').eq( 1 ); // La segunda fila del encabezado
				//console.log( fila_encabezado.find('th').eq(2).html() );
				//var celda_encabezado = $('table thead tr[1] th').eq( $(this).index() );
				var celda_encabezado = fila_encabezado.find('th').eq( $(this).index() );
				var etiqueta_mostrar = $(this).parent('tr').attr('title') + ": " + celda_encabezado.attr('title');
				$(this).attr( 'title', etiqueta_mostrar );
			});

		});
	</script>
@endsection