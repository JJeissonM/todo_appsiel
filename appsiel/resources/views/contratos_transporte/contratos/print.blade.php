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
			font-family: 'Lato';
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
	</style>
</head>

<body id="app-layout">
	<div class="container-fluid">
		<div class="row">
			<table class="table table-bordered table-striped">
				<tbody>
					<tr>
						<td class="border" style="width: 120px;"><img style="width: 120px; height: 80px;" src="{{ asset('img/logos/transporcol_back_contrato.jpg') }}"></td>
						<td class="border" style="width: 400px; text-align: center;">
							<table style="width: 100%;">
								<tbody>
									<tr>
										<td style="border-right: 1px solid;">
											<div style="font-size: 26px; line-height: 0.9em; text-align: center;">
												<p style="font-weight: bold; color: #000;">{{$emp->descripcion}}</p>
												<p style="font-weight: bold; color: #000;">{{$emp->razon_social}}</p>
												<p style="font-size: 20px; font-weight: bold; color: #000;">NIT: {{$emp->numero_identificacion."-".$emp->digito_verificacion}}</p>
											</div>
										</td>
										<td>
											<div>
												<p style="padding-left: 10px;">Código: {{$c->codigo}}<br>
													Versión: {{$c->version}}<br>
													Fecha: {{$c->fecha}}<br></p>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
							<table style="width: 100%; border-top: 1px solid;">
								<tbody>
									<tr>
										<td style="text-align: center;">
											<p style="font-size: 18px; font-weight: bold;">CONTRATO</p>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
						<td class="border" style="width: 120px;"><img style="width: 120px; height: 110px;" src="{{ asset('img/logos/transporcol_rigth.jpg') }}"></td>
					</tr>
				</tbody>
			</table>
			<div class="row" style="margin-top: 20px;">
				<div class="col-md-12" style="text-align: center; font-weight: bold; font-size: 20px;">
					<p><b>CONTRATO DE PRESTACION DE SERVICIO DE TRANSPORTE N° {{$c->numero_contrato}}</b></p>
				</div>
				<div class="col-md-12" style="margin-top: 20px; text-align: justify; font-size: 14px; padding: 40px !important;">
					<p>Entre los suscritos a saber <b>{{$c->rep_legal}}</b>
						en representación de la empresa <b>{{$emp->descripcion}}</b> con Nit. <b>{{$emp->numero_identificacion."-".$emp->digito_verificacion}}</b>, legalmente constituida
						y habilitada por el ministerio de transporte para la prestación del servicio transporte
						especial, de aquí en adelante el <b>CONTRATISTA</b>, y por otro lado <b>EL CONTRATANTE {{$c->contratante->tercero->descripcion}}</b> identificado con cedula <b>N° {{$c->contratante->tercero->numero_identificacion}}</b>
						en representación de <b>{{$c->representacion_de}}</b>
					</p>
					<h4>DESCRIPCIÓN DEL GRUPO DE USUARIOS</h4>
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Nro.</th>
								<th>Identificación</th>
								<th>Persona</th>
							</tr>
						</thead>
						<tbody>
							@if(count($c->contratogrupous)>0)
							<?php $i = 1; ?>
							@foreach($c->contratogrupous as $p)
							<tr>
								<td>{{$i}}</td>
								<td>{{$p->identificacion}}</td>
								<td>{{$p->persona}}</td>
							</tr>
							<?php $i = $i + 1; ?>
							@endforeach
							@endif
						</tbody>
					</table>
					<p>
						El presente contrato será desarrollado por el propietario del vehículo automotor de <b>PLACA {{$c->vehiculo->placa. ", MOVIL INTERNO " .$c->vehiculo->int. ", CAPACIDAD " . $c->vehiculo->capacidad}}</b>
						quien cumplirá todas las obligaciones derivadas del mismo. Hemos convenido celebrar el contrato de
						<b>TRANSPORTE DE GRUPO DE USUARIOS</b>, el cual se regirá por las siguientes clausula, y en lo no previsto en ellas, por lo dispuesto en la ley.
						<b>CLAUSULA PRIMERA - OBJETO DEL CONTRATO:</b> {{$c->objeto}} <b>CLAUSULA SEGUNDA: CARACTERISTICA DEL SERVICIO.</b>
						<b>ORIGEN </b>{{$c->origen}} <b>DESTINO </b>{{$c->destino}}
						<b>FECHA DE INICIAL </b>{{$c->fecha_inicio}} <b>FECHA VENCIMIENTO</b> {{$c->fecha_fin}}
						<b>CLAUSULA TERCERA. OBLIGACION DEL CONTRATANTE:</b> El <b>CONTARTANTE</b> se
						obliga con el <b>CONTARISTA</b>, a lo siguiente: <b>A)</b> Dar aviso de los servicios de transporte
						requerido con la suficiente anticipación, indicando claramente número de pasajeros,
						destino y demás detalles del servicios <b>B)</b> Cumplir con lo establecido en el presente
						contrato en forma oportuna, dentro de los términos establecidos y de conformidad con las
						calidades pactadas. <b>C)</b> Pagar el valor de la contraprestación en los términos y condiciones
						establecidas en este contrato. <b>D)</b> A cancelar los valores pactados para la ejecución del
						contrato de transporte que hace referencia este documento.
					</p>
					<table>
						<tbody>
							<tr>
								<td>Valor del Contrato </td>
								<td>$ {{$c->valor_contrato}}</td>
							</tr>
							<tr>
								<td>Valor cancelado a la empresa </td>
								<td>$ {{$c->valor_empresa}}</td>
							</tr>
							<tr>
								<td>Valor Cancelado al Propietario </td>
								<td>$ {{$c->valor_propietario}}</td>
							</tr>
						</tbody>
					</table>
					<p>
						<b>CLAUSULA CUARTA. OBLIGACION DEL CONTRATISTA:</b> El <b>CONTARTISTA</b> se
						obliga con el <b>CONTARTANTE A)</b> Cumplir con lo establecido en el presente contrato en
						forma oportuna, dentro del término establecido y de conformidad con las calidades
						pactadas <b>B)</b> Prestar el servicios en el vehículo arriba descrito que cumpla con todas las
						exigencias del ministerio de transporte y cumplir con las disposiciones legales
						contempladas en la ley 769 del 6 de agosto del 2002, el decreto 174 de 5 de febrero 2001
						<b>C)</b> Cumplir estrictamente con idoneidad y oportunidad en la ejecución del presente
						contrato. <b>CLAUSULA QUINTA. TERMINACION:</b> El presente contrato podrás darse por
						terminado por mutuo acuerdo entre las partes, sin lugar a indemnización alguna; o en
						forma unilateral por cumplimiento de las obligaciones derivadas del contrato; o bien, por
						que desaparezca las condiciones que le dieron origen sea por parte del <b>CONTRATANTE</b>
						o el <b>CONTARTISTA. CLAUSULA SEXTA. CESION</b> el presente contrato se celebra en
						consideración a la calidad del <b>CONTRATISTA</b>, quien no lo podrá ceder a subcontratar
						total o parcialmente sin consentimiento previo y por escrito del <b>CONTRATANTE.
							CLAUSULA SEPTIMA. INDEPENDENCIA DELA CONTARTISTA:</b> para todos los efectos
						legas, el presente contrato es de carácter civil y, en consecuencia el contratista, actuara
						por su propia cuenta, con absoluta autonomía y no estará sometido a subordinación
						laboral con el <b>CONTRATANTE</b>, para quien, sus derecho se limitaran, de acuerdo con la
						naturaleza del contrato, a exigir el cumplimiento de las obligaciones del <b>CONTRATISTA</b>,
						tendrá plena libertad y autonomía en la ejecución y cumplimiento de este contrato y en
						ningún momento tendrá relación laboral con el <b>CONTRATANTE. CLAUSULA OCTAVA.
							MODIFICACIONES:</b> el presente contrato podrá ser modificado por acuerdo entre las
						partes, mediante la suscripción de documento que indique con claridad y precisión la
						forma acordada. <b>CLAUSULA NOVENA. DOMICILIO CONTRACTUAL;</b> las notificaciones
						serán recibidas por las partes en las siguiente direcciones <b>CONTRATANTE
							{{$c->direccion_notificacion}} TELEFONO o CELULAR
							{{$c->telefono_notificacion}} CONTRATISTA</b> Carrera 10 # 16B - 29 Local 2 Segundo Piso
						<b>TELEFONO o CELULAR 572269 - 3186128754 – 3223039437.</b>
					</p>
					<p>
						En señal de aceptación, se firma el presente documento a los {{$c->dia_contrato}} días del mes de
						{{$c->mes_contrato}}.
					</p>
				</div>
			</div>
			<br><br><br>
			<table style="width: 100%; padding: 20px !important;">
				<tbody>
					<tr>
						<td style="width: 40%; text-align: left; font-weight: bold;">EL CONTRATANTE</td>
						<td style="width: 20%; text-align: left;"></td>
						<td style="width: 40%; text-align: left; font-weight: bold;">EL CONTRATISTA</td>
					</tr>
					<tr>
						<td style="width: 40%; text-align: left;"><br><br><br><br></td>
						<td style="width: 20%; text-align: left;"><br><br><br><br></td>
						<td style="width: 40%; text-align: left;"><br><br><br><br></td>
					</tr>
					<tr>
						<td style="width: 40%; text-align: left; border-bottom: 1px solid;"></td>
						<td style="width: 20%; text-align: left;"></td>
						<td style="width: 40%; text-align: left; border-bottom: 1px solid;"></td>
					</tr>
					<tr>
						<td style="width: 40%; text-align: left;">CC/NIT</td>
						<td style="width: 20%; text-align: left;"></td>
						<td style="width: 40%; text-align: left;">CC/NIT</td>
					</tr>
					<tr>
						<td style="width: 40%; text-align: left;">Firma</td>
						<td style="width: 20%; text-align: left;"></td>
						<td style="width: 40%; text-align: left;">Firma</td>
					</tr>
				</tbody>
			</table>
			<div class="col-md-12" style="margin-top: 50px; border-bottom: 10px solid; border-color: #6cf5ee;"></div>
			<div class="row" style="margin-top: 50px;">
				<div style="margin-top: 30px; text-align: center; width: 90%; float: left;">
					<p>
						<b>{{$c->pie_uno}}</b><br>
						<b>{{$c->pie_dos}}</b><br>
						<b>{{$c->pie_tres}}</b><br>
						<b style="color: #265a88; text-decoration: underline #265a88;">{{$c->pie_cuatro}}</b>
					</p>
				</div>
				<div style="margin-top: 50px; text-align: center; width: 10%; float: right;">
					<img style="width: 50px;" src="{{ asset('img/logos/super_transporte.png') }}">
				</div>
			</div>
		</div>
	</div>
</body>

</html>