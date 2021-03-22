@extends('core.procesos.layout')

@section( 'titulo', 'Listado de Congratulations' )

@section('detalles')
	<p>
		Este proceso genera el listado de Congratulations de estudiantes, en un periodo determinado, de los cursos asignados al docente. 
	</p>
	<p class="text-info">
		Nota: El sistema mostrara las valoraciones y observaciones que tengan un calificacion Alta.
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
						{{ Form::open(['url'=>'sga_observador_evaluacion_por_aspectos_congratulations','id'=>'formulario_inicial']) }}
							<div class="row">
								<div class="col-sm-4">
									{{ Form::label('semana_calendario_id','Semanas de evaluacion') }}
									<br/>
									{{ Form::select('semana_calendario_id',\App\Core\SemanasCalendario::opciones_campo_select(),null,[ 'class' => 'form-control', 'id' => 'semana_calendario_id', 'required' => 'required' ]) }}
								</div>
								<div class="col-sm-3">
									<!-- { { Form::label('curso_id','Curso') }}
									<br/>
									{ { Form::select('curso_id',\App\Matriculas\Curso::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'curso_id', 'required' => 'required' ]) }} -->
								</div>
								<div class="col-sm-3">
									<!--{ { Form::label('asignatura_id','Asignatura') }}
									<br/>
									{ { Form::select('asignatura_id',[],null, [ 'class' => 'form-control', 'id' => 'asignatura_id', 'required' => 'required' ]) }} -->
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

	{{ Form::bsBtnExcelV2('Listado de congratulations') }}
	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#btn_generar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		    	$('#div_form_cambiar').hide();
		    	$('#btn_excel_v2').hide();

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
        			$('#btn_excel_v2').show(500);

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
        			$("#checkbox_head").focus();
			    });
		    });

		});
	</script>
@endsection