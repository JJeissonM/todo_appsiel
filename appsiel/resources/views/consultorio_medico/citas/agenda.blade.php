@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<div class="row">
			<div class="col-md-12" style="padding-top: 30px;">
				<div class="col-md-7">
					<div class="panel panel-primary">
						<div class="panel-heading">Agenda Disponible Para la Fecha [[ {{$fecha}} ]]</div>
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
							<div class="col-md-12" style="padding-top: 30px;">
								<div class="table-responsive">
									<table id="myTable" class="table table-striped table-responsive">
										<thead>
											<tr>
												<th>Hora Inicio</th>
												<th>Hora Fin</th>
												<th>Consultorio</th>
												<th><i style="color: red; font-size: 16px;" class="fa fa-trash-o"></i></th>
											</tr>
										</thead>
										<tbody>
											@if($agendas!=null)
											@foreach($agendas as $a)
											<tr>
												<td>{{$a->hora_inicio}}</td>
												<td>{{$a->hora_fin}}</td>
												<td>{{$a->consultorio->descripcion." - SEDE: ".$a->consultorio->sede}}</td>
												<td>
													<a href="{{route('citas_medicas.delete',$a->id).$variables_url}}" title="Eliminar Entrada" class="btn btn-danger btn-xs"><i class="fa fa-trash-o"></i></a>
												</td>
											</tr>
											@endforeach
											@else
											<tr class="danger">
												<td>No hay entradas de agenda para la fecha indicada</td>
												<td></td>
												<td></td>
												<td></td>
											</tr>
											@endif
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-5">
					<div class="panel panel-primary">
						<div class="panel-heading">Crear Entrada de Agenda Para la Fecha [[ {{$fecha}} ]]</div>
						<div class="panel-body">
							{{ Form::open(['route'=>'citas_medicas.store','method'=>'post','class'=>'form-horizontal']) }}
							<input type="hidden" name="variables_url" value="{{$variables_url}}" />
							<div class="form-group">
								<div class="col-md-12">
									<label>Fecha Entrada</label>
									<input type="date" class="form-control" name="fecha" required>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-12">
									<label>Hora Inicio Atención</label>
									<input type="time" class="form-control" name="hora_inicio" required>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-12">
									<label>Hora Fin Atención</label>
									<input type="time" class="form-control" name="hora_fin" required>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-12">
									<label>Entrada de Agenda Para el Consultorio</label>
									<select class="form-control select2" name="consultorio_id" required>
										@if($consultorios!=null)
										@foreach($consultorios as $key=>$value)
										<option value="{{$key}}">{!!$value!!}</option>
										@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-12" style="margin-top: 50px;">
									<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Entrada de Agenda</button>
								</div>
							</div>
							</form>
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
		$('.select2').select2();
	});

	function cambiar() {
		var f = $("#fecha").val();
		if (f != '') {
			location.href = "{{url('')}}/citas_medicas/" + f + "?id={{$app}}&id_modelo={{$modelo}}";
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