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
				<!--<div class="col-md-12" style="margin-bottom: 40px;">
					<a href="{ {route('cte_contratos.planillacreate',[$c->id,$source]).$variables_url}}" class="btn btn-primary"><i class="fa fa-arrow-right"></i> GENERAR FUEC PARA ESTE CONTRATO</a>
				</div>-->
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
					<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Planillas Generadas al Contrato</h4>
					<table class="table table-bordered table-striped">
						<thead>
							<tr style=" vertical-align: middle !important;">
								<th>Nro. FUEC</th>
								<th>Título Plantilla</th>
								<th>Fecha Generada</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							@if(count($planillas)>0)
								@foreach($planillas as $p)
									<?php 
										if ( $c->numero_fuec == null ) {
											continue;
										}
									?>
									<tr>
										<td>{{$c->numero_fuec}}</td>
										<td>{{$p->plantilla->titulo}}</td>
									<td>{{$p->created_at}}</td>
									<td>
										<a target="_blank" href="{{ url('/cte_contratos/planillas/' . $p->id . '/imprimir')}}" class="btn-gmail" title="IMPRIMIR CONTRATO"><i class="fa fa-print"></i></a>
											@if($c->estado == 'ANULADO')
												<span class="label label-danger">ANULADO</span>
											@endif
										@if(Auth::user()->can('cte_fuec.anular'))
											@if($c->estado == 'ANULADO')
												&nbsp;
											@else
												<a href="#" class="btn-gmail btn-anular-fuec" data-action="{{ route('cte_contratos.anular_fuec', $c->id).$variables_url }}" data-fuec="{{ $c->numero_fuec }}" title="ANULAR FUEC"><i class="fa fa-times"></i></a>
											@endif
										@endif
									</td>
								</tr>
								@endforeach
							@endif
							@if(count($fuec_adicionales)>0)
								@foreach($fuec_adicionales as $fuec_adicional)
								<tr>
									<td>{{$fuec_adicional->numero_fuec}}</td>
									<td>{{$fuec_adicional->contrato->planillacs->first()->plantilla->titulo}}</td>
									<td>{{$fuec_adicional->created_at}}</td>
									<td>
										<a target="_blank" href="{{route('cte_contratos_fuec_adicional.imprimir',$fuec_adicional->id)}}" class="btn-gmail" title="IMPRIMIR FUEC"><i class="fa fa-print"></i></a>

										@if($fuec_adicional->estado == 'ANULADO')
												<span class="label label-danger">ANULADO</span>
											@endif

										@if(Auth::user()->can('cte_fuec.anular'))
											@if($fuec_adicional->estado == 'ANULADO')
												&nbsp;
											@else
												<a href="#" class="btn-gmail btn-anular-fuec" data-action="{{ route('cte_contratos_fuec_adicional.anular_fuec', $fuec_adicional->id).$variables_url }}" data-fuec="{{ $fuec_adicional->numero_fuec }}" title="ANULAR FUEC"><i class="fa fa-times"></i></a>
											@endif
										@endif
									</td>
								</tr>
								@endforeach
							@endif
							<tr>
								<td colspan="4">
									<a href="{{route('cte_contratos_fuec_adicional.create',['contrato_id='.$c->id, 'id=19', 'modelo_id=197',  'source=' .Input::get('source') ])}}" class="btn btn-primary btn-md"><i class="fa fa-plus"></i> Crear nuevo FUEC</a>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_anular_fuec" tabindex="-1" role="dialog" aria-labelledby="modalAnularFuecLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modalAnularFuecLabel">Anular FUEC <span id="modal_fuec_numero"></span></h4>
			</div>
			<div class="modal-body">
				<p>Indique el motivo de la anulacion. Esta accion no se puede deshacer.</p>
				<form method="POST" id="form_anular_fuec">
					{{ csrf_field() }}
					<div class="form-group">
						<label for="motivo_anulacion">Motivo</label>
						<textarea class="form-control" name="motivo_anulacion" id="motivo_anulacion" rows="3" required></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-danger" id="btn_confirmar_anulacion">Anular</button>
			</div>
		</div>
	</div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		//$('.select2').select2();

		$('.btn-anular-fuec').on('click', function() {
			var action = $(this).data('action');
			var fuec = $(this).data('fuec');
			$('#form_anular_fuec').attr('action', action);
			$('#modal_fuec_numero').text('#' + fuec);
			$('#motivo_anulacion').val('');
			$('#modal_anular_fuec').modal('show');
		});

		$('#btn_confirmar_anulacion').on('click', function() {
			var motivo = $.trim($('#motivo_anulacion').val());
			if (motivo.length === 0) {
				alert('El campo motivo anulacion es obligatorio.');
				$('#motivo_anulacion').focus();
				return;
			}
			$('#form_anular_fuec').submit();
		});
	});
</script>
@endsection
