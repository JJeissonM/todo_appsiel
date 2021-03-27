@extends('core.procesos.layout')

@section( 'titulo', 'Reporte de Consolidados pos curso' )

@section('seccion_encabezado')

	<a class="btn btn-info btn-md" href="{{url('/index_procesos/matriculas.procesos.listado_congratulations?id=' . Input::get('id') )}}" title="Listado de Congratulations"><i class="fa fa-list"></i> Listado de Congratulations </a>
	<a class="btn btn-warning btn-md" href="{{url('/index_procesos/matriculas.procesos.generar_estadisticas_evaluacion_aspectos_por_curso?id=' . Input::get('id') )}}" title="Estadísticas por curso"><i class="fa fa-pie-chart"></i> Estadísticas por curso </a>

	<br><br>

@endsection

@section('detalles')
	<p>
		Este proceso genera el reporte de consoliados por Curso. 
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
						{{ Form::open(['url'=>'sga_observador_evaluacion_por_aspectos_reporte_consolidados','id'=>'formulario_inicial']) }}
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
									&nbsp;
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


	{{ Form::bsBtnPdf( 'reporte_consolidados_evaluacion_por_aspectos' ) }}

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){


			var URL = "{{ url('/') }}";

			var url_pdf_ori = $('#btn_pdf').attr('href');

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
				$('#btn_pdf').hide();

				$('#btn_pdf').attr('href', url_pdf_ori);

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


					$('#btn_pdf').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace( 'a3p0', 'generar_pdf/consolidados_evaluacion_por_aspectos' + '?tam_hoja=folio' + '&orientacion=landscape' );
					}
					
					$('#btn_pdf').attr('href', new_url);

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