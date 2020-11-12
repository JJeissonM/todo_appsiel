@extends('layouts.principal')
<?php

use App\Http\Controllers\ContratoTransporte\ContratoTransporteController;
?>
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
						<a href="#" class="list-group-item list-group-item-action">Contratante: {{$c->contratante->tercero->descripcion}}</a>
						<a href="#" class="list-group-item list-group-item-action">Vehículo: {{"INTERNO: ".$c->vehiculo->int." - PLACA: ".$c->vehiculo->placa." - MODELO: ".$c->vehiculo->modelo." - MARCA: ".$c->vehiculo->marca." - CLASE: ".$c->vehiculo->clase}}</a>
						<a href="#" class="list-group-item list-group-item-action">Propietario Vehículo: {{$c->vehiculo->propietario->tercero->descripcion}}</a>
					</div>
				</div>
				<div class="table-responsive col-md-12" id="table_content">
					<h3>Generar Planilla</h3>
					<h4><b>Nota: </b>Seleccione los conductores y proceda a generar la planilla en el botón GENERAR PLANILLA ubicado al final de la página</h4>
					<div class="col-md-12">
						<div class="list-group">
							<a href="#" class="list-group-item list-group-item-action active">
								Parte Frontal de la Planilla
							</a>
						</div>
						<div class="col-md-12" style="padding: 50px;">
							<div class="col-md-12 page">
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 40%;"><img style="width: 100%;" src="{{ asset('img/logos/min_transporte.png') }}"></td>
											<td class="border" style="width: 20%; text-align: center;"><img style="width: 70%;" src="{{ asset('img/logos/super_transporte.png') }}"></td>
											<td class="border" style="width: 40%;"><img style="width: 100%;" src="{{ asset('img/logos/transporcol_center.png') }}"></td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 100%; text-align: center; font-weight: bold;">{{$v->titulo}} <br> N° {{$nro}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 20%; font-weight: bold;">RAZÓN SOCIAL</td>
											<td class="border" style="width: 50%;">{{$e->descripcion}}</td>
											<td class="border" style="width: 10%; font-weight: bold;">NIT</td>
											<td class="border" style="width: 20%;">{{$e->numero_identificacion."-".$e->digito_verificacion}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 20%; font-weight: bold;">CONTRATO No.</td>
											<td class="border" style="width: 80%;">{{$c->numero_contrato}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 20%; font-weight: bold;">CONTRATANTE</td>
											<td class="border" style="width: 50%; font-weight: bold;">{{$c->contratante->tercero->descripcion." ".$c->contratante->tercero->razon_social}}</td>
											<td class="border" style="width: 10%; font-weight: bold;">NIT/CC</td>
											<td class="border" style="width: 20%; font-weight: bold;">{{$c->contratante->tercero->numero_identificacion}} @if($c->contratante->tercero->tipo!='Persona natural') {{"-".$c->contratante->tercero->digito_verificacion}} @endif</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 20%; font-weight: bold;">OBJETO CONTRATO</td>
											<td class="border" style="width: 80%; font-size: 12px;">{{$c->objeto}}</td>
										</tr>
										<tr>
											<td class="border" style="width: 20%; font-weight: bold;">ORIGEN - DESTINO</td>
											<td class="border" style="width: 80%; font-weight: bold;">{{$c->origen." - ".$c->destino}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 100%; font-weight: bold;">CONVENIO CONSORCIO UNION TEMPORAL CON: </td>
										</tr>
										<tr>
											<td class="border" style="width: 100%; font-weight: bold; text-align: center;">VIGENCIA DEL CONTRATO</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 30%;"></td>
											<td class="border" style="width: 20%; font-weight: bold;">DÍA</td>
											<td class="border" style="width: 20%; font-weight: bold;">MES</td>
											<td class="border" style="width: 20%; font-weight: bold;">AÑO</td>
										</tr>
										<tr>
											<td class="border" style="width: 30%; font-weight: bold;">FECHA INICIAL</td>
											<td class="border" style="width: 20%; font-weight: bold;">{{$fi[2]}}</td>
											<td class="border" style="width: 20%; font-weight: bold;">{{ContratoTransporteController::mes()[$fi[1]]}}</td>
											<td class="border" style="width: 20%; font-weight: bold;">{{$fi[0]}}</td>
										</tr>
										<tr>
											<td class="border" style="width: 30%; font-weight: bold;">FECHA FINAL</td>
											<td class="border" style="width: 20%; font-weight: bold;">{{$ff[2]}}</td>
											<td class="border" style="width: 20%; font-weight: bold;">{{ContratoTransporteController::mes()[$ff[1]]}}</td>
											<td class="border" style="width: 20%; font-weight: bold;">{{$ff[0]}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 100%; font-weight: bold; text-align: center;">CARACTERÍSTICAS DEL VEHÍCULO</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 15%; font-weight: bold;">PLACA</td>
											<td class="border" style="width: 25%; font-weight: bold;">MODELO</td>
											<td class="border" style="width: 20%; font-weight: bold;">MARCA</td>
											<td class="border" style="width: 40%; font-weight: bold;">CLASE</td>
										</tr>
										<tr>
											<td class="border" style="width: 15%; font-weight: bold;">{{$c->vehiculo->placa}}</td>
											<td class="border" style="width: 25%; font-weight: bold;">{{$c->vehiculo->modelo}}</td>
											<td class="border" style="width: 20%; font-weight: bold;">{{$c->vehiculo->marca}}</td>
											<td class="border" style="width: 40%; font-weight: bold;">{{$c->vehiculo->clase}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 40%; font-weight: bold;">NÚMERO INTERNO</td>
											<td class="border" style="width: 60%; font-weight: bold;">NÚMERO TARJETA DE OPERACIÓN</td>
										</tr>
										<tr>
											<td class="border" style="width: 40%; font-weight: bold;">{{$c->vehiculo->int}}</td>
											<td class="border" style="width: 60%; font-weight: bold;">@if($to!=null) {{$to->nro_documento}} @else --- @endif</td>
										</tr>
									</tbody>
								</table>
								{{ Form::open(['route'=>'cte_contratos.planillastore','method'=>'post','class'=>'form-horizontal','id'=>'form']) }}
								<input type="hidden" name="variables_url" value="{{$variables_url}}" />
								<input type="hidden" name="id" value="{{$c->id}}" />
								<input type="hidden" name="source" value="{{$source}}" />
								<input type="hidden" name="plantilla_id" value="{{$v->id}}" />
								<input type="hidden" name="nro" value="{{$nro}}" />
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 15%; font-weight: bold;"></td>
											<td class="border" style="width: 85%; font-weight: bold;">SELECCIONE CONDUCTOR</td>
										</tr>
										<tr>
											<td class="border" style="width: 15%; font-weight: bold; font-size: 14px; text-align: center;">DATOS DEL CONDUCTOR 1</td>
											<td class="border" style="width: 85%; font-weight: bold; font-size: 14px;">
												<select name="conductor_id[]" class="form-control select2">
													<option value="">-- Seleccione opción --</option>
													@if($conductores!=null)
													@foreach($conductores as $key=>$value)
													<option value="{{$key}}">{{$value}}</option>
													@endforeach
													@endif
												</select>
											</td>
										</tr>
										<tr>
											<td class="border" style="width: 15%; font-weight: bold; font-size: 14px; text-align: center;">DATOS DEL CONDUCTOR 2</td>
											<td class="border" style="width: 85%; font-weight: bold; font-size: 14px;">
												<select name="conductor_id[]" class="form-control select2">
													<option value="">-- Seleccione opción --</option>
													@if($conductores!=null)
													@foreach($conductores as $key=>$value)
													<option value="{{$key}}">{{$value}}</option>
													@endforeach
													@endif
												</select>
											</td>
										</tr>
										<tr>
											<td class="border" style="width: 15%; font-weight: bold; font-size: 14px; text-align: center;">DATOS DEL CONDUCTOR 3</td>
											<td class="border" style="width: 85%; font-weight: bold; font-size: 14px;">
												<select name="conductor_id[]" class="form-control select2">
													<option value="">-- Seleccione opción --</option>
													@if($conductores!=null)
													@foreach($conductores as $key=>$value)
													<option value="{{$key}}">{{$value}}</option>
													@endforeach
													@endif
												</select>
											</td>
										</tr>
									</tbody>
								</table>
								</form>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 15%; font-weight: bold;"></td>
											<td class="border" style="width: 40%; font-weight: bold;">NOMBRES Y APELLIDOS</td>
											<td class="border" style="width: 13%; font-weight: bold;">No CÉDULA</td>
											<td class="border" style="width: 22%; font-weight: bold;">DIRECCIÓN</td>
											<td class="border" style="width: 10%; font-weight: bold;">TELÉFONO</td>
										</tr>
										<tr>
											<td class="border" style="width: 15%; font-weight: bold; font-size: 14px; text-align: center;">RESPONSABLE DEL CONTRATANTE</td>
											<td class="border" style="width: 40%; font-weight: bold; font-size: 14px;">{{$c->contratante->tercero->descripcion." ".$c->contratante->tercero->razon_social}}</td>
											<td class="border" style="width: 13%; font-weight: bold; font-size: 14px;">{{$c->contratante->tercero->numero_identificacion}} @if($c->contratante->tercero->tipo!='Persona natural') {{"-".$c->contratante->tercero->digito_verificacion}} @endif</td>
											<td class="border" style="width: 22%; font-weight: bold; font-size: 14px;">{{$c->contratante->tercero->direccion1}}</td>
											<td class="border" style="width: 10%; font-weight: bold; font-size: 14px;">{{$c->contratante->tercero->telefono1}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 40%; text-align: center; font-weight: bold; padding: 10px;">{{$v->direccion}}<br>{{$v->telefono}}<br><a>{{$v->correo}}</a></td>
											<td class="border" style="width: 20%; text-align: center; font-weight: bold;" valign="bottom">Sello</td>
											<td class="border" style="width: 40%; text-align: center; font-weight: bold; font-size: 14px;" valign="bottom">FIRMA<br>{{$v->firma}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 100%; text-align: justify; font-size: 14px;">{!!$v->pie_pagina1!!}</a></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="list-group">
							<a href="#" class="list-group-item list-group-item-action active">
								Parte Posterior de la Planilla
							</a>
						</div>
						<div class="col-md-12" style="padding: 50px;">
							<div class="col-md-12 page">
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 40%;"><img style="width: 100%;" src="{{ asset('img/logos/min_transporte.png') }}"></td>
											<td class="border" style="width: 20%; text-align: center;"><img style="width: 70%;" src="{{ asset('img/logos/super_transporte.png') }}"></td>
											<td class="border" style="width: 40%;"><img style="width: 100%;" src="{{ asset('img/logos/transporcol_center.png') }}"></td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 100%; text-align: center; font-weight: bold;">{{$v->titulo}} <br> N° {{$nro}}</td>
										</tr>
									</tbody>
								</table>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 100%; padding: 50px;">
												<p style=" text-align: center; font-weight: bold; font-size: 16px;">{{$v->titulo_atras}}</p>
												@if(count($v->plantillaarticulos)>0)
												@foreach($v->plantillaarticulos as $a)
												<p style="text-align: justify;"><b>{{$a->titulo}}</b> {{$a->texto}}</p>
												@if(count($a->plantillaarticulonumerals)>0)
												@foreach($a->plantillaarticulonumerals as $pan)
												<p style="text-align: justify;"><b>{{$pan->numeracion}}</b> {{$pan->texto}}</p>
												@if(count($pan->numeraltablas)>0)
												<?php $total = count($pan->numeraltablas);
												$mitad = 0;
												if ($total % 2 == 0) {
													$mitad = $total / 2;
												} else {
													$mitad = $total / 2;
													$mitad = $mitad + 0.5;
												}
												?>
												<div class="col-md-12" style="margin-bottom: 15px;">
													<table style="width: 50%; float: left;">
														<tbody>
															<?php $i = 0; ?>
															@foreach($pan->numeraltablas as $n)
															<?php $i = $i + 1; ?>
															@if($i<=$mitad) <tr>
																<td class="border">{{$n->campo}}</td>
																<td class="border">{{$n->valor}}</td>
										</tr>
										@endif
										@endforeach
									</tbody>
								</table>
								<table style="width: 50%; float: right;">
									<tbody>
										<?php $i = 0; ?>
										@foreach($pan->numeraltablas as $n)
										<?php $i = $i + 1; ?>
										@if($i>$mitad)
										<tr>
											<td class="border">{{$n->campo}}</td>
											<td class="border">{{$n->valor}}</td>
										</tr>
										@endif
										@endforeach
									</tbody>
								</table>
							</div>
							@endif
							@endforeach
							@endif
							@endforeach
							@else
							<p>No hay artículos en la plantilla</p>
							@endif
							</td>
							</tr>
							</tbody>
							</table>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-12" style="margin-top: 50px;">
							<button onclick="ir()" class="btn btn-primary"><i class="fa fa-save"></i> GENERAR PLANILLA</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$('.select2').select2();
	});


	function ir() {
		$("#form").submit();
	}
</script>
@endsection