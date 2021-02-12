@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<div class="row">
			@if( Auth::user()->hasRole('Vehículo (FUEC)') )
				<div class="col-md-12 botones-gmail">
    				{{ Form::bsBtnCreate( url( 'cte_contratos/create?id=19&id_modelo=197&id_transaccion=&source=MISCONTRATOS' ) ) }}
    			</div>
			@endif
    	</div>
		<div class="row">
			<div class="col-md-12" style="padding-top: 30px;">
				<div class="col-md-6">
					<div class="panel panel-primary">
						<h4 style="padding: 15px;">Contratos Este Mes ({{$mes_actual}})</h4>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-responsive">
									<thead>
										<tr style="background-color: #50B794; vertical-align: middle !important;">
											<th>Nro.</th>
											<th>Origen - Destino</th>
											<th>Vigencia</th>
											<th>Contratante</th>
											<th>Imprimir</th>
										</tr>
									</thead>
									<tbody>
										@if($contratos!=null)
											@foreach($contratos as $c)
												<tr>
													<td>{{$c->numero_contrato}}</td>
													<td>{{$c->origen." - ".$c->destino}}</td>
													<td>{{"DESDE: ".$c->fecha_inicio." - HASTA: ".$c->fecha_fin}}</td>
													<td>@if($c->contratante_id==null || $c->contratante_id=='null') {{$c->contratanteText}} @else {{$c->contratante->tercero->descripcion}} @endif</td>
													<td>
														@if($c->estado=='ANULADO')
														<p>ANULADO</p>
														@else
														<a target="_blank" href="{{route('cte_contratos.imprimir',$c->id)}}" class="btn-gmail" title="Imprimir"><i class="fa fa-print"></i></a>
														@endif
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
				<div class="col-md-6">
					<div class="panel panel-primary">
						<h4 style="padding: 15px;">Documentos Vencidos</h4>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-responsive">
									<thead>
										<tr style="background-color: #50B794; vertical-align: middle !important;">
											<th>Nro. Documento</th>
											<th>Documento - Categoría</th>
											<th>Vigencia</th>
											<th>Conductor</th>
										</tr>
									</thead>
									<tbody>
										@if($documentos!=null)
										@foreach($documentos as $d)
										<tr>
											<td>{{$d->nro_documento}}</td>
											<td>{{$d->documento}} @if($d->categoria!=null) {{" - CATEGORIA: ".$d->categoria}} @endif</td>
											<td>{{"DESDE: ".$d->vigencia_inicio." - HASTA: ".$d->vigencia_fin}}</td>
											<td>{{$d->conductor->tercero->descripcion}}</td>
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
		</div>
	</div>
</div>

<br />
@endsection