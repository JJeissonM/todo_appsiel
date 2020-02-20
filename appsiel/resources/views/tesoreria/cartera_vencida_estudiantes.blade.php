@extends('layouts.principal')

<?php use App\Http\Controllers\Core\ConfiguracionController; ?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<?php 
					echo Lava::render('PieChart', 'torta_matriculas', 'torta_matriculas');
					echo Lava::render('PieChart', 'torta_pensiones', 'torta_pensiones');
				?>
			<h2><b>Estado de cuentas pendientes por mes</b></h2>

			<div class="row">
				<div class="col-md-6">
					<br><br>

					<div class="row" style="padding:5px;">
						{{ Form::bsSelect( 'curso_id', $curso_id, 'Curso',$cursos, [ ] ) }}
			        </div>

					<div class="row" style="padding:5px; text-align: center;">
						<a href="{{ url('tesoreria/cartera_vencida_estudiantes?id='.Input::get('id').'&curso_id='.Input::get('curso_id')) }}" class="btn btn-info btn-bg" id="btn_actualizar">Actualizar</a>
			        </div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<h3>Matrículas</h3>
					<div id="torta_matriculas"></div>
					
					<div class="table-responsive">
						<h2>Cartera de Matrículas</h2>
						<table class="table table-bordered table-striped">
							{{ Form::bsTableHeader(['Mes','Valor','Acción']) }}
							<tbody>
								
									<?php
									$num_mes="01";
									for($i=0;$i<12;$i++){
							            if (strlen($num_mes)==1) {
							                $num_mes="0".$num_mes;
							            }
							            $url='tesoreria/imprimir_cartera/matricula/mes/'.$num_mes.'?curso_id='.Input::get('curso_id');
							            echo "<tr>
							            <td>".ConfiguracionController::nombre_mes($num_mes)."</td>
							             <td>$".number_format($cartera_matriculas[$num_mes], 0, ',', '.')."</td>";
							             if($cartera_matriculas[$num_mes]>0){
							             	echo "<td><a class='btn btn-info btn-xs btn-detail' href='".url($url)."'><i class='fa fa-btn fa-print'></i>&nbsp;Imprimir cartera</a></td></tr>";
							             }else{
							             	echo "<td>&nbsp;</td></tr>";
							             }
							             
							            $num_mes++;
							            if($num_mes>=13){
							                $num_mes='01';
							            }
							        }
							        ?>
								
							</tbody>
						</table>
					</div>
				</div>

				<div class="col-md-6">
					<h3>Pensiones</h3>
					<div id="torta_pensiones"></div>
					
					<div class="table-responsive">
						<h2>Cartera de Pensiones</h2>
						<table class="table table-bordered table-striped">
							{{ Form::bsTableHeader(['Mes','Valor','Acción']) }}
							<tbody>
								
									<?php
									$num_mes="01";
									for($i=0;$i<12;$i++){
							            if (strlen($num_mes)==1) {
							                $num_mes="0".$num_mes;
							            }
							            $url='tesoreria/imprimir_cartera/pension/mes/'.$num_mes.'?curso_id='.Input::get('curso_id');
							            echo "<tr>
							            <td>".ConfiguracionController::nombre_mes($num_mes)."</td>
							             <td>$".number_format($cartera_pensiones[$num_mes], 0, ',', '.')."</td>";
							             if($cartera_pensiones[$num_mes]>0){
							             	echo "<td><a class='btn btn-info btn-xs btn-detail' href='".url($url)."'><i class='fa fa-btn fa-print'></i>&nbsp;Imprimir cartera</a></td></tr>";
							             }else{
							             	echo "<td>&nbsp;</td></tr>";
							             }

							            $num_mes++;
							            if($num_mes>=13){
							                $num_mes='01';
							            }
							        }
							        ?>
								
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			var id = getParameterByName('id');

			$('#curso_id').on('change',function()
			{
				var url = '<?php echo url('/tesoreria/cartera_vencida_estudiantes'); ?>';
				$('#btn_actualizar').attr('href', url + '?id='+id+'&curso_id='+$('#curso_id').val() );
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

