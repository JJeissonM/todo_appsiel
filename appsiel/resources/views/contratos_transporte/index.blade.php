@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<div class="row">
			<div class="col-md-12" style="padding-top: 30px;">
				<div class="col-md-6">
					<div class="panel panel-primary">
						<div class="panel-heading">Contratos Este Mes ({{$mes_actual}})</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table id="myTable" class="table table-striped table-responsive">
									<thead>
										<tr>
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
											<td>{{$c->contratante->tercero->descripcion}}</td>
											<td>
												@if($c->estado=='ANULADO')
												<p>ANULADO</p>
												@else
												<a target="_blank" href="{{route('cte_contratos.imprimir',$c->id)}}" class="btn btn-primary btn-xs"><i class="fa fa-print"></i></a>
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
						<div class="panel-heading">Documentos Vencidos</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-responsive">
									<thead>
										<tr>
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