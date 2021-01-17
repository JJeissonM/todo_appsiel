@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-12">
				<div class="list-group">
					<a style="font-size: 18px;" href="#" class="list-group-item list-group-item-action active">
						Listado de vehículos (puede filtrar por propietarios para que solo aparezcan los vehículos de dicho propietario y hacer más fácil la busqueda)
					</a>
				</div>
			</div>
			<div class="col-md-12">
				<div class="table-responsive" id="table_content">
					<table class="table table-bordered table-striped">
						<thead>
							<tr style="background-color: #50B794; vertical-align: middle !important;">
								<th>Interno</th>
								<th>Vinculación</th>
								<th>Placa</th>
								<th>Marca</th>
								<th>Clase</th>
								<th>Modelo</th>
								<th>Propietario</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							@foreach($vehiculos as $v)
							<tr>
								<td>{{$v->int}}</td>
								<td>{{$v->numero_vin}}</td>
								<td>{{$v->placa}}</td>
								<td>{{$v->marca}}</td>
								<td>{{$v->clase}}</td>
								<td>{{$v->modelo}}</td>
								<td>{{$v->propietario->tercero->numero_identificacion." - ".$v->propietario->tercero->descripcion." ".$v->propietario->tercero->razon_social}}</td>
								<td>
									<a href="{{route('mantenimiento.continuar',$v->id).$variables_url}}" class="btn-gmail" title="Gestionar Mantenimientos del Vehículo"><i class="fa fa-arrow-right"></i></a>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script>

</script>
@endsection