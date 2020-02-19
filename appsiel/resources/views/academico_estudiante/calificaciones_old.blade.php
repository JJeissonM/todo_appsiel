@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<h3 align="center">Mis Calificaciones</h3>

	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable">
			{{ Form::bsTableHeader($encabezado_tabla) }}
			<tbody>
				@foreach ($registros as $fila)
					<tr>
						<?php for($i=1;$i<count($fila)-2;$i++){ ?>
							@if($i!=7)
								<td class="table-text">
									{{ $fila['campo'.$i] }}
								</td>
							@else
								<?php
									// campo7 es ID

									if ( count($fila['campo6']) > 0 ) 
									{
										$escala = DB::table('sga_escala_valoracion')
										->where('calificacion_minima','<=',$fila['campo6'])
										->where('calificacion_maxima','>=',$fila['campo6'])->get()[0];
									}else{
										$calificacion = (object) array('calificacion' => 0);
										$escala = (object) array('id' => 0, 'nombre_escala' => '');
										//$escala = 'NO';
									}

									$desempeno = $escala->nombre_escala;

									if ( count($escala) > 0 ) 
									{
										$logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)->where('curso_id',$fila['campo9'])->where('asignatura_id',$fila['campo10'])->where('estado','Activo')->get();

										$n_nom_logros = count($logros);
									}else{
										$logros = (object) array('descripcion' => '');
										$n_nom_logros = 0;
									}
									
									$tbody = '<td ';if($n_nom_logros==0){ $tbody.='style="background-color:#F08282; color:white;"';}
									$tbody.='>
											<ul>';
											foreach($logros as $un_logro)
											{
												$tbody.='<li>'.$un_logro->descripcion.'</li>';
											}		
									$tbody.='</ul>
										</td>';

									echo $tbody;
								?>
							@endif
						<?php } ?>
							<td>
								&nbsp;
							</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endsection