<?php

use App\Http\Controllers\ContratoTransporte\ContratoTransporteController;
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>
		APPSIEL
	</title>

	<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

	<!-- Fonts -->
	<!-- Styles -->
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

	<link rel="stylesheet" href="{{ asset('assets/css/mis_estilos.css') }}">

	<style>
		body {
			font-family: 'Arial';
			background-color: #FFFFFF !important;
			/*width: 98%;*/
		}

		#suggestions {
			position: absolute;
			z-index: 9999;
		}

		#proveedores_suggestions {
			position: absolute;
			z-index: 9999;
		}

		a.list-group-item-sugerencia {
			cursor: pointer;
		}

		/*
		#existencia_actual, #tasa_impuesto{
			width: 35px;
		}
		*/

		.custom-combobox {
			position: relative;
			display: inline-block;
		}

		.custom-combobox-toggle {
			position: absolute;
			top: 0;
			bottom: 0;
			margin-left: -1px;
			padding: 0;
		}

		.custom-combobox-input {
			margin: 0;
			padding: 5px 10px;
		}

		#div_cargando {
			display: none;
			/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed;
			/*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom: 0px;
			/*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index: 999;
			width: 100%;
			text-align: center;
		}

		#popup_alerta_danger {
			display: none;
			/**/
			color: #FFFFFF;
			background: red;
			border-radius: 5px;
			position: fixed;
			/*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right: 10px;
			/*A la izquierda deje un espacio de 0px*/
			bottom: 10px;
			/*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index: 999999;
			float: right;
			text-align: center;
			padding: 5px;
			opacity: 0.7;
		}

		#popup_alerta_success {
			display: none;
			/**/
			color: #FFFFFF;
			background: #55b196;
			border-radius: 5px;
			position: fixed;
			/*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right: 10px;
			/*A la izquierda deje un espacio de 0px*/
			bottom: 10px;
			/*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index: 999999;
			float: right;
			text-align: center;
			padding: 5px;
			opacity: 0.7;
		}

		.border {
			border: 1px solid;
			padding: 5px;
		}

		.page-break{
			page-break-after: always;
		}

	</style>
</head>

<body id="app-layout">
	<div class="container-fluid">
		<div class="row" style="font-size: 13px; line-height: 1.1;">
			<div class="col-md-12">
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 40%;"><img style="width: 260px; height: 80px;" src="{{ asset('img/logos/min_transporte.png') }}"></td>
							<!--<td class="border" style="width: 20%; text-align: center;"><img style="width: 80px; height: 80px;" src="{{ asset('img/logos/super_transporte.png') }}"></td>-->
							<td class="border" style="width: 20%; text-align: center;"><img src="data:image/png;base64,{{DNS2D::getBarcodePNG($url, 'QRCODE')}}" alt="barcode"/></td>
							<td class="border" style="width: 40%;"><img style="width: 260px; height: 80px;" src="{{ asset('img/logos/transporcol_center.png') }}"></td>
						</tr>
					</tbody>
				</table>
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 100%; text-align: center; font-weight: bold;">{{$v->titulo}} <br> N° {{$p->nro}}</td>
						</tr>
					</tbody>
				</table>
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 20%; font-weight: bold;">RAZÓN SOCIAL</td>
							<td class="border" style="width: 50%;">{{$p->razon_social}}</td>
							<td class="border" style="width: 10%; font-weight: bold;">NIT</td>
							<td class="border" style="width: 20%;">{{$p->nit}}</td>
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
							<td class="border" style="width: 50%; font-weight: bold;">@if($c->contratante_id==null || $c->contratante_id=='null') {{$c->contratanteText}} @else {{$c->contratante->tercero->descripcion." ".$c->contratante->tercero->razon_social}} @endif</td>
							<td class="border" style="width: 10%; font-weight: bold;">NIT/CC</td>
							<td class="border" style="width: 20%; font-weight: bold;">@if($c->contratante_id==null || $c->contratante_id=='null') @else {{$c->contratante->tercero->numero_identificacion}} @if($c->contratante->tercero->tipo!='Persona natural') {{"-".$c->contratante->tercero->digito_verificacion}} @endif @endif</td>
						</tr>
					</tbody>
				</table>
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 20%; font-weight: bold; border-right: none;">OBJETO CONTRATO:</td>
							<td class="border" style="width: 80%; font-size: 12px; border-left: none;">{{strtoupper($c->objeto)}}</td>
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
							<td class="border" style="width: 30%; border-bottom: none;"></td>
							<td class="border" style="width: 20%; font-weight: bold;">DÍA</td>
							<td class="border" style="width: 20%; font-weight: bold;">MES</td>
							<td class="border" style="width: 20%; font-weight: bold;">AÑO</td>
						</tr>
						<tr>
							<td class="border" style="width: 30%; font-weight: bold; border-top: none;">FECHA INICIAL</td>
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
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 15%; font-weight: bold; font-size: 12px; border-bottom: none;"></td>
							<td class="border" style="width: 32%; font-weight: bold; font-size: 12px;">NOMBRES Y APELLIDOS</td>
							<td class="border" style="width: 13%; font-weight: bold; font-size: 12px;">No CÉDULA</td>
							<td class="border" style="width: 19%; font-weight: bold; font-size: 12px;">No LICENCIA CONDUCCIÓN</td>
							<td class="border" style="width: 10%; font-weight: bold; font-size: 12px;">VIGENCIA</td>
						</tr>
						<tr>
							<td class="border" style="width: 15%; font-weight: bold; font-size: 12px; text-align: center; border-top: none;">DATOS DEL CONDUCTOR 1</td>
							<td class="border" style="width: 32%; font-weight: bold; font-size: 12px;">@if(isset($conductores[0])){{$conductores[0]->conductor->tercero->descripcion}}@endif</td>
							<td class="border" style="width: 13%; font-weight: bold; font-size: 12px;">@if(isset($conductores[0])){{$conductores[0]->conductor->tercero->numero_identificacion}}@endif</td>
							<td class="border" style="width: 19%; font-weight: bold; font-size: 12px;">@if(isset($conductores[0])) @if($conductores[0]->licencia!=null) {{$conductores[0]->licencia->nro_documento}} @endif @endif</td>
							<td class="border" style="width: 10%; font-weight: bold; font-size: 12px;">@if(isset($conductores[0])) @if($conductores[0]->licencia!=null) {{$conductores[0]->licencia->vigencia_fin}} @endif @endif</td>
						</tr>
						<tr>
							<td class="border" style="width: 15%; font-weight: bold; font-size: 12px; text-align: center;">DATOS DEL CONDUCTOR 2</td>
							<td class="border" style="width: 32%; font-weight: bold; font-size: 12px;">@if(isset($conductores[1])){{$conductores[1]->conductor->tercero->descripcion}}@endif</td>
							<td class="border" style="width: 13%; font-weight: bold; font-size: 12px;">@if(isset($conductores[1])){{$conductores[1]->conductor->tercero->numero_identificacion}}@endif</td>
							<td class="border" style="width: 19%; font-weight: bold; font-size: 12px;">@if(isset($conductores[1])) @if($conductores[1]->licencia!=null) {{$conductores[0]->licencia->nro_documento}} @endif @endif</td>
							<td class="border" style="width: 10%; font-weight: bold; font-size: 12px;">@if(isset($conductores[1])) @if($conductores[1]->licencia!=null) {{$conductores[0]->licencia->vigencia_fin}} @endif @endif</td>
						</tr>
						<tr>
							<td class="border" style="width: 15%; font-weight: bold; font-size: 12px; text-align: center;">DATOS DEL CONDUCTOR 3</td>
							<td class="border" style="width: 32%; font-weight: bold; font-size: 12px;">@if(isset($conductores[2])){{$conductores[2]->conductor->tercero->descripcion}}@endif</td>
							<td class="border" style="width: 13%; font-weight: bold; font-size: 12px;">@if(isset($conductores[2])){{$conductores[2]->conductor->tercero->numero_identificacion}}@endif</td>
							<td class="border" style="width: 19%; font-weight: bold; font-size: 12px;">@if(isset($conductores[2])) @if($conductores[2]->licencia!=null) {{$conductores[0]->licencia->nro_documento}} @endif @endif</td>
							<td class="border" style="width: 10%; font-weight: bold; font-size: 12px;">@if(isset($conductores[2])) @if($conductores[2]->licencia!=null) {{$conductores[0]->licencia->vigencia_fin}} @endif @endif</td>
						</tr>
						<tr>
							<td class="border" style="width: 15%; font-weight: bold; font-size: 12px; border-bottom: none;"></td>
							<td class="border" style="width: 32%; font-weight: bold; font-size: 12px;">NOMBRES Y APELLIDOS</td>
							<td class="border" style="width: 13%; font-weight: bold; font-size: 12px;">No CÉDULA</td>
							<td class="border" style="width: 19%; font-weight: bold; font-size: 12px;">DIRECCIÓN</td>
							<td class="border" style="width: 10%; font-weight: bold; font-size: 12px;">TELÉFONO</td>
						</tr>
						<tr>
							<td class="border" style="width: 15%; font-weight: bold; font-size: 12px; text-align: center; border-top: none;">RESPONSABLE DEL CONTRATANTE</td>
							<td class="border" style="width: 32%; font-weight: bold; font-size: 12px;">@if($c->contratante_id==null || $c->contratante_id=='null') {{$c->contratanteText}} @else {{$c->contratante->tercero->descripcion." ".$c->contratante->tercero->razon_social}} @endif</td>
							<td class="border" style="width: 13%; font-weight: bold; font-size: 12px;">@if($c->contratante_id==null || $c->contratante_id=='null') @else {{$c->contratante->tercero->numero_identificacion}} @if($c->contratante->tercero->tipo!='Persona natural') {{"-".$c->contratante->tercero->digito_verificacion}} @endif @endif</td>
							<td class="border" style="width: 19%; font-weight: bold; font-size: 12px;">@if($c->contratante_id==null || $c->contratante_id=='null') @else {{$c->contratante->tercero->direccion1}} @endif</td>
							<td class="border" style="width: 10%; font-weight: bold; font-size: 12px;">@if($c->contratante_id==null || $c->contratante_id=='null') @else {{$c->contratante->tercero->telefono1}} @endif</td>
						</tr>
					</tbody>
				</table>
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 40%; text-align: center; font-weight: bold; margin-top: 15px !important;">@if($empresa!=null) {{$empresa->direccion1." - "}} @endif {{$v->direccion}}<br> @if($empresa!=null) {{$empresa->telefono1." - "}} @endif {{$v->telefono}}<br><a> @if($empresa!=null) {{$empresa->email." - "}} @endif {{$v->correo}}</a></td>
							<td class="border" style="width: 20%; text-align: center; font-weight: bold; margin-top: 50px !important;" valign="bottom"><br><br>Sello</td>
							<td class="border" style="width: 40%; text-align: center; font-weight: bold; margin-top: 50px !important; font-size: 14px;" valign="bottom"><br><br>FIRMA<br><i style="font-size: 9px; text-decoration: none;"  valign="bottom">{{$v->firma}}</i></td>
						</tr>
					</tbody>
				</table>
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 100%; text-align: justify; font-size: 10px;">{!!$v->pie_pagina1!!}</a></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="page-break"></div>
			<div class="col-md-12" style="font-size: 12px !important; line-height: 1.1;">
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 40%;"><img style="width: 260px; height: 80px;" src="{{ asset('img/logos/min_transporte.png') }}"></td>
							<td class="border" style="width: 20%; text-align: center;"><img style="width: 80px; height: 80px;" src="{{ asset('img/logos/super_transporte.png') }}"></td>
							<td class="border" style="width: 40%;"><img style="width: 260px; height: 80px;" src="{{ asset('img/logos/transporcol_center.png') }}"></td>
						</tr>
					</tbody>
				</table>
				<table style="width: 100%;">
					<tbody>
						<tr>
							<td class="border" style="width: 100%; text-align: center; font-weight: bold;">{{$v->titulo}} <br> N° {{$p->nro}}</td>
						</tr>
					</tbody>
				</table>
				<table style="width: 100%; line-height: 0.7;">
					<tbody>
						<tr>
							<td class="border" style="width: 100%; padding: 10px; font-size: 10px">
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
													<table style="width: 100%;">
														<tbody>
															<tr>
																<td>
																	<table style="width: 100%;">
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
																</td>
																<td>
																	<table style="width: 100%;">
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
																</td>
															</tr>
														</tbody>
													</table>
													<br>
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
</body>

</html>