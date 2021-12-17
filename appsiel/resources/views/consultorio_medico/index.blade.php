@extends('layouts.principal')

<?php 
	$ultimas_consultas = App\Salud\ConsultaMedica::orderBy('id','DESC')->take(10)->get();
	//dd($ultimas_consultas);
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	{!! $select_crear !!}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			<div class="row">
				<div class="col-md-4">
					<h5>Últimos pacientes atendidos</h5>
					<hr>
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Fecha consulta</th>
								<th>Paciente</th>
								<th>Atendido por</th>
							</tr>
						</thead>
						<tbody>
						@foreach($ultimas_consultas as $consulta)
							
								<tr>
									<td>{{$consulta->fecha}}</td>
									<td>
										<a href="{{url('consultorio_medico/pacientes/') . '/' . $consulta->paciente_id . '?id=18&id_modelo=95&id_transaccion='}}">
											{{$consulta->paciente->tercero->descripcion}}
										</a>
									</td>
									<td>{{$consulta->profesional_salud->tercero->descripcion}}</td>
								</tr>
						@endforeach
						</tbody>
					</table>
				</div>
				<div class="col-md-4">
					
				</div>
				<div class="col-md-4">
					
				</div>
				<div class="col-md-4">
					
				</div>
			</div>

		</div>
	</div>

	<br/>
@endsection