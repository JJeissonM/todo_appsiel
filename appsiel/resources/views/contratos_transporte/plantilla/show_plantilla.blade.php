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
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Parte Frontal de la Plantilla
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
									<td class="border" style="width: 100%; text-align: center; font-weight: bold;">{{$v->titulo}} <br> N° XXXXXXXXXXXXXXXXXXXXX</td>
								</tr>
							</tbody>
						</table>
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td class="border" style="width: 20%; font-weight: bold;">RAZÓN SOCIAL</td>
									<td class="border" style="width: 50%;">ASOCIACIÓN TRANSPORCOL</td>
									<td class="border" style="width: 10%; font-weight: bold;">NIT</td>
									<td class="border" style="width: 20%;">900.293.125-3</td>
								</tr>
							</tbody>
						</table>
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td class="border" style="width: 20%; font-weight: bold;">CONTRATO No.</td>
									<td class="border" style="width: 80%;">ASOCIACIÓN TRANSPORCOL</td>
								</tr>
							</tbody>
						</table>
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td class="border" style="width: 20%; font-weight: bold;">CONTRATANTE</td>
									<td class="border" style="width: 50%; font-weight: bold;"></td>
									<td class="border" style="width: 10%; font-weight: bold;">NIT/CC</td>
									<td class="border" style="width: 20%; font-weight: bold;"></td>
								</tr>
							</tbody>
						</table>
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td class="border" style="width: 20%; font-weight: bold;">OBJETO CONTRATO</td>
									<td class="border" style="width: 80%; font-size: 12px;"></td>
								</tr>
								<tr>
									<td class="border" style="width: 20%; font-weight: bold;">ORIGEN - DESTINO</td>
									<td class="border" style="width: 80%; font-weight: bold;"></td>
								</tr>
							</tbody>
						</table>
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td class="border" style="width: 100%; font-weight: bold;">CONVENIO CONSORCIO UNION TEMPORAL CON</td>
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
									<td class="border" style="width: 20%; font-weight: bold;"></td>
									<td class="border" style="width: 20%; font-weight: bold;"></td>
									<td class="border" style="width: 20%; font-weight: bold;"></td>
								</tr>
								<tr>
									<td class="border" style="width: 30%; font-weight: bold;">FECHA FINAL</td>
									<td class="border" style="width: 20%; font-weight: bold;"></td>
									<td class="border" style="width: 20%; font-weight: bold;"></td>
									<td class="border" style="width: 20%; font-weight: bold;"></td>
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
									<td class="border" style="width: 15%; font-weight: bold;">&InvisibleComma;</td>
									<td class="border" style="width: 25%; font-weight: bold;"></td>
									<td class="border" style="width: 20%; font-weight: bold;"></td>
									<td class="border" style="width: 40%; font-weight: bold;"></td>
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
									<td class="border" style="width: 40%; font-weight: bold;">&InvisibleComma;</td>
									<td class="border" style="width: 60%; font-weight: bold;"></td>
								</tr>
							</tbody>
						</table>
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td class="border" style="width: 15%; font-weight: bold;"></td>
									<td class="border" style="width: 40%; font-weight: bold;">NOMBRES Y APELLIDOS</td>
									<td class="border" style="width: 13%; font-weight: bold;">No CÉDULA</td>
									<td class="border" style="width: 22%; font-weight: bold;">No LICENCIA CONDUCCIÓN</td>
									<td class="border" style="width: 10%; font-weight: bold;">VIGENCIA</td>
								</tr>
								<tr>
									<td class="border" style="width: 15%; font-weight: bold; font-size: 14px; text-align: center;">DATOS DEL CONDUCTOR 1</td>
									<td class="border" style="width: 40%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 13%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 22%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 10%; font-weight: bold; font-size: 14px;"></td>
								</tr>
								<tr>
									<td class="border" style="width: 15%; font-weight: bold; font-size: 14px; text-align: center;">DATOS DEL CONDUCTOR 2</td>
									<td class="border" style="width: 40%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 13%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 22%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 10%; font-weight: bold; font-size: 14px;"></td>
								</tr>
								<tr>
									<td class="border" style="width: 15%; font-weight: bold; font-size: 14px; text-align: center;">DATOS DEL CONDUCTOR 3</td>
									<td class="border" style="width: 40%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 13%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 22%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 10%; font-weight: bold; font-size: 14px;"></td>
								</tr>
								<tr>
									<td class="border" style="width: 15%; font-weight: bold;"></td>
									<td class="border" style="width: 40%; font-weight: bold;">NOMBRES Y APELLIDOS</td>
									<td class="border" style="width: 13%; font-weight: bold;">No CÉDULA</td>
									<td class="border" style="width: 22%; font-weight: bold;">DIRECCIÓN</td>
									<td class="border" style="width: 10%; font-weight: bold;">TELÉFONO</td>
								</tr>
								<tr>
									<td class="border" style="width: 15%; font-weight: bold; font-size: 14px; text-align: center;">RESPONSABLE DEL CONTRATANTE</td>
									<td class="border" style="width: 40%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 13%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 22%; font-weight: bold; font-size: 14px;"></td>
									<td class="border" style="width: 10%; font-weight: bold; font-size: 14px;"></td>
								</tr>
							</tbody>
						</table>
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td class="border" style="width: 40%; text-align: center; font-weight: bold; padding: 10px;">@if($empresa!=null) {{$empresa->direccion1." - "}} @endif {{$v->direccion}}<br> @if($empresa!=null) {{$empresa->telefono1." - "}} @endif {{$v->telefono}}<br><a> @if($empresa!=null) {{$empresa->email." - "}} @endif {{$v->correo}}</a></td>
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
						Parte Posterior de la Plantilla
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
									<td class="border" style="width: 100%; text-align: center; font-weight: bold;">{{$v->titulo}} <br> N° XXXXXXXXXXXXXXXXXXXXX</td>
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
												@if($i<=$mitad)
												<tr>
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
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script>

</script>
@endsection