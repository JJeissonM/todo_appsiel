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
							            $url='tesoreria/imprimir_cartera/matricula/mes/'.$num_mes;
							            echo "<tr>
							            <td>".ConfiguracionController::nombre_mes($num_mes)."</td>
							             <td class='text-right'>$".number_format($cartera_matriculas[$num_mes], 0, ',', '.')."</td>";
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
							            $url='tesoreria/imprimir_cartera/pension/mes/'.$num_mes;
							            echo "<tr>
							            <td>".ConfiguracionController::nombre_mes($num_mes)."</td>
							             <td class='text-right'>$".number_format($cartera_pensiones[$num_mes], 0, ',', '.')."</td>";
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

