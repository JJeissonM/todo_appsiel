@extends('layouts.principal')

@section('webstyle')
<style>
	.page {
		padding: 50px;
		-webkit-box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		-moz-box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		font-size: 16px;
	}

	.border {
		border: 1px solid;
		padding: 5px;
	}
</style>
@endsection

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-12">
				<div class="col-md-12" style="margin-bottom: 40px;">
					<a href="{{route('cte_contratos.planillacreate',[$c->id,$source]).$variables_url}}" class="btn btn-primary"><i class="fa fa-arrow-right"></i> GENERAR FUEC PARA ESTE CONTRATO</a>
				</div>
				<div class="col-md-6">
					<div class="list-group">
						<a href="#" class="list-group-item list-group-item-action">Nro. Contrato: {{$c->numero_contrato}}</a>
						<a href="#" class="list-group-item list-group-item-action">Objeto Contrato: {{$c->objeto}}</a>
						<a href="#" class="list-group-item list-group-item-action">Fecha Celebrado: {{"DÍA: ".$c->dia_contrato." - MES: ".$c->mes_contrato}}</a>
						<a href="#" class="list-group-item list-group-item-action">Origen - Destino: {{$c->origen." - ".$c->destino}}</a>
					</div>
				</div>
				<div class="col-md-6">
					<div class="list-group">
						<a href="#" class="list-group-item list-group-item-action">Vigencia: {{"DESDE ".$c->fecha_inicio." HASTA ".$c->fecha_fin}}</a>
						<a href="#" class="list-group-item list-group-item-action">Contratante: @if($c->contratante_id==null || $c->contratante_id=='null') {{$c->contratanteText}} @else {{$c->contratante->tercero->descripcion}} @endif</a>
						<a href="#" class="list-group-item list-group-item-action">Vehículo: {{"INTERNO: ".$c->vehiculo->int." - PLACA: ".$c->vehiculo->placa." - MODELO: ".$c->vehiculo->modelo." - MARCA: ".$c->vehiculo->marca." - CLASE: ".$c->vehiculo->clase}}</a>
						<a href="#" class="list-group-item list-group-item-action">Propietario Vehículo: {{$c->vehiculo->propietario->tercero->descripcion}}</a>
					</div>
				</div>
				<div class="table-responsive col-md-12" id="table_content">
					<h3>Planillas Generadas al Contrato</h3>
					<table class="table table-bordered table-striped" id="myTable">
						<thead>
							<tr>
								<th>Id Planilla</th>
								<th>Título Plantilla</th>
								<th>Fecha Generada</th>
								<th>Imprimir FUEC</th>
							</tr>
						</thead>
						<tbody>
							@if(count($planillas)>0)
							@foreach($planillas as $p)
							<tr>
								<td>{{$p->id}}</td>
								<td>{{$p->plantilla->titulo}}</td>
								<td>{{$p->created_at}}</td>
								<td>
									<a target="_blank" href="{{route('cte_contratos.planillaimprimir',$p->id)}}" class="btn btn-xs btn-success"><i class="fa fa-print"></i> IMPRIMIR FUEC</a>
									<a target="_blank" href="{{route('cte_contratos.imprimir',$c->id)}}" class="btn btn-xs btn-primary"><i class="fa fa-print"></i> IMPRIMIR CONTRATO</a>
								</td>
							</tr>
							@endforeach
							@endif
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
	$(document).ready(function() {
		//$('.select2').select2();
	});
</script>
@endsection