@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;

	if ( $valor_total == 0) {
		$valor_total = 0.0000001;
	}
?>

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
						{{ Form::bsSelect( 'periodo_id', $periodo_id, 'Periodo',$periodos, [ ] ) }}
			        </div>

					<div class="row" style="padding:5px;">
						{{ Form::bsSelect( 'curso_id', $curso_id, 'Curso',$cursos, [ ] ) }}
			        </div>

					<div class="row" style="padding:5px; text-align: center;">
						<a href="{{ url('calificaciones?id='.Input::get('id').'&periodo_id='.Input::get('periodo_id').'&curso_id='.Input::get('curso_id')) }}" class="btn btn-info btn-bg" id="btn_actualizar">Actualizar</a>
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
								<th>Calificación promedio</th>
								<th>Porcentaje</th>
							</tr>
						</thead>
						<tbody>
							@for($i=0; $i < $cant; $i++)
								<tr>
									<td>{{ $tabla[$i]['escala'] }}</td>
									<td>{{ number_format($tabla[$i]['valor'], 2, ',', '.') }}</td>
									<td>{{ number_format(($tabla[$i]['valor'] * 100) / $valor_total, 2, ',', '.') }} %</td>
								</tr>
							@endfor		
						</tbody>
					</table>
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

			$('#periodo_id').on('change',function()
			{
				$('#btn_actualizar').attr('href','calificaciones?id='+id+'&periodo_id='+$('#periodo_id').val()+'&curso_id='+$('#curso_id').val());					
			});

			$('#curso_id').on('change',function()
			{
				$('#btn_actualizar').attr('href','calificaciones?id='+id+'&periodo_id='+$('#periodo_id').val()+'&curso_id='+$('#curso_id').val());					
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