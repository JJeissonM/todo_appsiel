@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<div class="row">
			<div class="col-md-12" style="padding-top: 30px;">
				<div class="col-md-12">
					<div class="panel panel-primary">
						<div class="panel-heading">Mis Citas Programadas Para la Fecha [[ {{$fecha}} ]]</div>
						<div class="panel-body">
							<div class="col-md-12">
								<div class="col-md-12">
									<label>Seleccionar Otra Fecha</label>
								</div>
								<div class="col-md-8">
									<input type="date" id="fecha" name="fecha" class="form-control">
								</div>
								<div class="col-md-4">
									<button class="btn btn-primary btn-block" onclick="cambiar()"><i class="fa fa-search"></i> Consultar Esta Fecha</button>
								</div>
							</div>
							<div class="col-md-12" style="margin-top: 50px;">
								<div class="table-responsive">
									<table class="table table-striped table-responsive">
										<thead>
											<tr>
												<th>Fecha</th>
												<th>Horario</th>
												<th>Consultorio</th>
												<th>Paciente</th>
												<th>Estado</th>
											</tr>
										</thead>
										<tbody>
											@if($data!=null)
											@foreach($data as $v)
											<tr>
												<td>{{$v['fecha']}}</td>
												<td>{{$v['hora_inicio']." - ".$v['hora_fin']}}</td>
												<td>{{$v['consultorio']}}</td>
												<td>{{$v['paciente']}}</td>
												<td>{{$v['estado']}}</td>
											</tr>
											@endforeach
											@else
											<tr class="danger">
												<td colspan="5">Usted no tiene citas programadas para la fecha indicada</td>
											</tr>
											@endif
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<br />


@endsection
@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		//
	});

	function cambiar() {
		var f = $("#fecha").val();
		if (f != '') {
			location.href = "{{url('')}}/citas_medicas/create?id={{$app}}&id_modelo={{$modelo}}&fecha=" + f;
		} else {
			Swal.fire({
				icon: 'error',
				title: 'Atención!',
				text: 'Debe indicar la fecha para proceder'
			});
		}
	}
</script>
@endsection