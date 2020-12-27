@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	{!! $select_crear !!}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<h4>Existencias por bodega</h4>
		    <hr>
		    <br/>
			<?php 
				for ($i=0; $i < $cantidad_graficas; $i++) { 
					$grafica = 'MyStocks_'.$i;
					echo Lava::render('BarChart', $grafica, 'div_chart_'.$i);
					echo '<div class="row">
								<div  class="col-md-12" style="border: 1px solid;">
									<b>'.$titulos[$i]['bodega_nombre'].' <a href="'.url('inv_consultar_existencias/'.$titulos[$i]['bodega_id'].'?id='.Input::get('id')).'&fecha_corte='.date('Y-m-d').'" title="Consultar existencias"><i class="fa fa-search"></i></a></b>
										<div id="div_chart_'.$i.'"></div>
								</div>
							</div><br/>';
				}
			?>
		</div>
	</div>

	<br/>
@endsection