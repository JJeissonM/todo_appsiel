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
				<h3>Listado de contratos celebrados sobre mis vehículos (PROPIETARIO) y/o sobre los vehículos a los cuales he sido asociado como CONDUCTOR</h3>
				<div class="table-responsive col-md-12" id="table_content">
					<table class="table table-bordered table-striped" id="myTable">
						<thead>
							<tr>
								<th>Nro.</th>
								<th>Objeto</th>
								<th>Fecha Celebrado</th>
								<th>Origen - Destino</th>
								<th>Vigencia</th>
								<th>Contratante</th>
								<th>Vehículo</th>
								<th>Contrato Como</th>
								<th>Planillas FUEC</th>
							</tr>
						</thead>
						<tbody>
							@if($contratos!=null)
							@foreach($contratos as $c)
							<tr>
								<td>{{$c['contrato']->numero_contrato}}</td>
								<td>{{$c['contrato']->objeto}}</td>
								<td>{{"DÍA: ".$c['contrato']->dia_contrato." - MES: ".$c['contrato']->mes_contrato}}</td>
								<td>{{$c['contrato']->origen." - ".$c['contrato']->destino}}</td>
								<td>{{"DESDE ".$c['contrato']->fecha_inicio." HASTA ".$c['contrato']->fecha_fin}}</td>
								<td>{{$c['contrato']->contratante->tercero->descripcion}}</td>
								<td>{{"INTERNO: ".$c['vehiculo']->int." - PLACA: ".$c['vehiculo']->placa." - MODELO: ".$c['vehiculo']->modelo." - MARCA: ".$c['vehiculo']->marca." - CLASE: ".$c['vehiculo']->clase}}</td>
								<td>{{$c['tipo']}}</td>
								<td>@if($c['genera']=='SI')<a href="{{route('cte_contratos.planillaindex',[$c['contrato']->id,'MISCONTRATOS']).$variables_url}}" class="btn btn-xs btn-primary"><i class="fa fa-arrow-right"></i></a>@else -- Usted no se encuentra activo en el sistema, no tiene licencia registrada o su licencia está vencida. No puede generar planillas -- @endif</td>
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