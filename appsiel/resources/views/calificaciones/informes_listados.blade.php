@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	<div class="container-fluid">
		<div class="marco_formulario">

			<h2>Rendimiento académico</h2>
			<div class="row">
				<div class="col-md-6">
					<br><br>
					<div class="row" style="padding:5px;">
						{{ Form::bsSelect( 'periodo_lectivo_id', $periodo_lectivo_id, 'Año lectivo',$periodos_lectivos, [ ] ) }}
			        </div>

					<div class="row" style="padding:5px;">
						{{ Form::bsSelect( 'periodo_id', $periodo_id, 'Periodo',$periodos, [ ] ) }}
			        </div>

					<div class="row" style="padding:5px;">
						{{ Form::bsSelect( 'curso_id', $curso_id, 'Curso',$cursos, [ ] ) }}
			        </div>

					<div class="row" style="padding:5px; text-align: center;">
						<a href="{{ url('calificaciones?id='.Input::get('id').'&periodo_lectivo_id='.$periodo_lectivo_id.'&periodo_id='.Input::get('periodo_id').'&curso_id='.Input::get('curso_id')) }}" class="btn btn-info btn-bg" id="btn_actualizar">Actualizar</a>
			        </div>



				</div>
				<div class="col-md-6">
					<?php 
						echo Lava::render('PieChart', 'rendimiento_academico', 'grafica1');
						$cant = count($tabla);


					?>
					<div id="grafica1"></div>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Escala</th>
								<th>Cantidad calificaciones</th>
								<th>Calificación promedio</th>
								<th>Porcentaje</th>
							</tr>
						</thead>
						<tbody>
							@for($i=0; $i < $cant; $i++)
								<tr>
									<td>{{ $tabla[$i]['escala'] }}</td>
									<td>{{ number_format($tabla[$i]['cantidad'], 0, ',', '.') }}</td>
									<td>{{ number_format($tabla[$i]['promedio'], 2, ',', '.') }}</td>
									<td>{{ $total_calificaciones > 0 ? number_format(($tabla[$i]['cantidad'] * 100) / $total_calificaciones, 2, ',', '.') : '0,00' }} %</td>
								</tr>
							@endfor		
						</tbody>
					</table>
					<div class="well well-sm" style="margin-top: 10px;">
						<strong>Interpretación del reporte:</strong> la gráfica muestra cómo se distribuyen las calificaciones registradas en las escalas de valoración del año lectivo, periodo y curso seleccionados. La columna <strong>Cantidad calificaciones</strong> indica cuántos registros quedaron en cada escala, <strong>Calificación promedio</strong> muestra el promedio de esas notas dentro de la misma escala y <strong>Porcentaje</strong> corresponde a la participación de cada escala sobre el total de calificaciones analizadas.
					</div>
				</div>
				
			</div>
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			var id = getParameterByName('id');
			var baseUrl = "{{ url('calificaciones') }}";

			function actualizarUrl()
			{
				$('#btn_actualizar').attr('href', baseUrl + '?id=' + id + '&periodo_lectivo_id=' + $('#periodo_lectivo_id').val() + '&periodo_id=' + $('#periodo_id').val() + '&curso_id=' + $('#curso_id').val());
			}

			$('#periodo_lectivo_id').on('change',function()
			{
				$('#periodo_id').html('<option value="">Todos</option>');
				$('#curso_id').html('<option value="">Todos</option>');

				var periodo_lectivo_id = $(this).val();

				actualizarUrl();

				if ( periodo_lectivo_id == '') {
					return false;
				}

				$.ajax({
		        	url: "{{ url('get_select_periodos') }}" + "/" + periodo_lectivo_id,
		        	type: 'get',
		        	success: function(datos){
	    				$('#periodo_id').html(datos.replace('Seleccionar...', 'Todos'));
	    				actualizarUrl();
			        }
			    });

				$.ajax({
		        	url: "{{ url('get_select_cursos') }}" + "/" + periodo_lectivo_id,
		        	type: 'get',
		        	success: function(datos){
	    				$('#curso_id').html(datos.replace('Seleccionar...', 'Todos'));
	    				actualizarUrl();
			        }
			    });
			});

			$('#periodo_id').on('change',function()
			{
				actualizarUrl();					
			});

			$('#curso_id').on('change',function()
			{
				actualizarUrl();					
			});

			actualizarUrl();

			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}

		});

	</script>
@endsection
