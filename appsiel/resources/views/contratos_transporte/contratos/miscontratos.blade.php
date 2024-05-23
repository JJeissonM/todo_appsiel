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
<div class="col-md-12 botones-gmail">
	<a href="{{route('cte_contratos.create').$variables_url.'&source=MISCONTRATOS'}}" class="btn-gmail" title="Crear Contrato Para FUEC"><i class="fa fa-plus"></i></a>
</div>
<hr>

@include('layouts.mensajes')
<div class="container-fluid">
	<div class="marco_formulario" style="margin-top: 20px;">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-12">
				<h5 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Listado de contratos celebrados sobre el vehículo {{"INTERNO: ".$v->int." - PLACA: ".$v->placa." - MODELO: ".$v->modelo." - MARCA: ".$v->marca." - CLASE: ".$v->clase}}</h5>
				<div class="table-responsive col-md-12" id="table_content">
					<table class="table table-bordered table-striped">
						<thead>
							<tr style=" vertical-align: middle !important;">
								<th>Nro.</th>
								<th>Objeto</th>
								<th>Fecha Celebrado</th>
								<th>Origen - Destino</th>
								<th>Vigencia</th>
								<th>Contratante</th>
								<th>Vehículo</th>
								<th width="150px">Planilla FUEC</th>
								<th width="150px">FUEC Adicionales</th>
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
								<td>
									@if($c['contrato']->contratante_id==null || $c['contrato']->contratante_id=='null') {{$c['contrato']->contratanteText}} @else {{$c['contrato']->contratante->tercero->descripcion}} @endif
								</td>
								<td>{{"INTERNO: ".$c['vehiculo']->int." - PLACA: ".$c['vehiculo']->placa." - MODELO: ".$c['vehiculo']->modelo." - MARCA: ".$c['vehiculo']->marca." - CLASE: ".$c['vehiculo']->clase}}</td>
								<td>
									@if($c['contrato']->estado=='ACTIVO')
										@if($c['bloqueado']=='NO')
											<a href="{{ url('/') . '/cte_contratos/' . $c['contrato']->id . '/show' . $variables_url}}" class="btn-gmail" title="Consultar"><i class="fa fa-eye"></i></a>

											<a target="_blank" href="{{route('cte_contratos.imprimir',$c['contrato']->id)}}" class="btn btn-info btn-md" title="IMPRIMIR CONTRATO Y FUEC"><i class="fa fa-print"></i> {{ $c['contrato']->numero_fuec }} </a>
										@else
										-- Usted no puede generar planillas --
										@endif
									@else
										-- ANULADO --
									@endif
								</td>
								<td>
									<?php 
										$fuec_adicionales = $c['contrato']->fuec_adicionales;
									?>
									@foreach($fuec_adicionales as $fuec_adicional)
										<a class="btn btn-info btn-md" target="_blank" href="{{route('cte_contratos_fuec_adicional.imprimir',$fuec_adicional->id)}}" style="margin: 5px;"  title="IMPRIMIR FUEC"> <i class="fa fa-print"></i> {{ $fuec_adicional->numero_fuec }}</a>
									@endforeach
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