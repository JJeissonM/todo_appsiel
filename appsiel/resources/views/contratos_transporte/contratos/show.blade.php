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
				<div class="panel panel-primary">
					<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Ver Contrato</h4>
					<div class="panel-body">
						<div class="col-md-12" style="padding: 50px;">
						<a href="{{ route('cte_contratos.planillaindex',[$c->id,$source]).$variables_url . '&source=' .$source }}" class="btn btn-primary">
							<i class="fa fa-arrow-right"></i> 
							GENERAR E IMPRIMIR PLANILLAS FUEC PARA ESTE CONTRATO
						</a>
							<div class="col-md-12 page" style="margin-top: 30px;">
								<h3 style="text-align: center;"><i class="fa fa-eye"></i> Vista Previa <i class="fa fa-eye"></i> </h3>
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 20%;"><img style="width: 100%;" src="{{ asset('img/logos/min_transporte.png') }}"></td>
											<td class="border" style="width: 68%; text-align: center;">
												<div class="col-md-8" style="border-right: 1px solid; font-size: 24px; line-height: 0.9em;">
													<p style="font-weight: bold; color: #000;">{{$e->descripcion}}</p>
													@if( $e->descripcion != $e->razon_social )
														
														<p style="font-weight: bold; color: #000;">{{$e->razon_social}}</p>
														
													@endif
													
													<p style="font-size: 20px; font-weight: bold; color: #000;">{{ config("configuracion.tipo_identificador") }} : {{$e->numero_identificacion."-".$e->digito_verificacion}}</p>
												</div>
												<div class="col-md-4" style="text-align: left;">
													Código: {{$c->codigo}}<br>
													Versión: {{$c->version}}<br>
													Fecha: {{$c->fecha}}<br>
												</div>
												<div class="col-md-12" style="border-top: 1px solid;">
													<p style="font-size: 20px; font-weight: bold;">CONTRATO</p>
												</div>
											</td>
											<td class="border" style="width: 12%;"><img style="max-height: 150px;" src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$e->imagen }}"></td>
										</tr>
									</tbody>
								</table>
								<div class="row" style="margin-top: 20px;">
									<div class="col-md-12" style="text-align: center;">
										<p><b>CONTRATO DE PRESTACION DE SERVICIO DE TRANSPORTE N° {{$c->numero_contrato}}</b></p>
									</div>
									<div class="col-md-12" style="margin-top: 20px;">
										<p>Entre los suscritos a saber <b>{{$c->rep_legal}}</b>
											en representación de la empresa <b>{{$e->descripcion}}</b> con Nit. <b>{{$e->numero_identificacion."-".$e->digito_verificacion}}</b>, legalmente constituida
											y habilitada por el ministerio de transporte para la prestación del servicio transporte
											especial, de aquí en adelante el <b>CONTRATISTA</b>, y por otro lado <b>EL CONTRATANTE @if($c->contratante_id==null || $c->contratante_id=='null') {{$c->contratanteText}} </b> @else {{$c->contratante->tercero->descripcion}}</b> identificado con cedula <b>N° {{$c->contratante->tercero->numero_identificacion}}</b> @endif
											en representación de <b>{{$c->representacion_de}}</b>
										</p>
										<div class="table-responsive col-md-12" id="table_content">
											<h4>LISTADO DE PASAJEROS DE LOS SERVICIOS CONTRATADOS</h4>
											<table class="table table-bordered table-striped">
												<thead>
													<tr>
														<th>Nro.</th>
														<th>Identificación</th>
														<th>Nombre Completo</th>
														<th>Eliminar del Contrato</th>
													</tr>
												</thead>
												<tbody>
													@if(count($c->contratogrupous)>0)
														<?php  
															$i = 1;
														?>
														@foreach($c->contratogrupous as $p)
														<tr>
															<td>{{$i}}</td>
															<td>{{$p->identificacion}}</td>
															<td>{{$p->persona}}</td>
															<td><a class="btn btn-xs btn-danger" href="{{route('cte_contratos.deletegu',$p->id).$variables_url}}"><i class="fa fa-trash-o"></i></a></td>
														</tr>
														<?php  
															$i++;
														?>
														@endforeach
													@endif
												</tbody>
											</table>
											<a onclick="addRow('usuarios')" class="btn btn-danger btn-xs"><i class="fa fa-plus"></i> Agregar pasajero</a>
											{{ Form::open(['route'=>'cte_contratos.storegu','method'=>'post','class'=>'form-horizontal','id'=>'form_agregar_usuario']) }}
											<input type="hidden" name="variables_url" value="{{$variables_url}}" />
											<input type="hidden" name="id" value="{{$c->id}}" />
											<table id="usuarios" class="table table-bordered table-striped">
												<thead>
													<tr>
														<th>Identificación</th>
														<th>Nombre Completo</th>
														<th>Agregar</th>
													</tr>
												</thead>
												<tbody>

												</tbody>
											</table>
											</form>
										</div>
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
								
								@include('contratos_transporte.contratos.tabla_firma_sello',['empresa'=>$e])

								<div class="col-md-12" style="margin-top: 50px; border-bottom: 10px solid; border-color: #6cf5ee;"></div>
								<div class="row" style="margin-top: 50px;">
									<div class="col-md-11" style="margin-top: 30px; text-align: center;">
										<p>
											<b>{{$c->pie_uno}}</b>
											<b>{{$c->pie_dos}}</b>
											<b>{{$c->pie_tres}}</b>
											<b>{{$c->pie_cuatro}}</b>
										</p>
									</div>
									<div class="col-md-1" style="margin-top: 50px; text-align: center;">
										<img style="width: 100%;" src="{{ asset('img/logos/super_transporte.png') }}">
									</div>
								</div>
							</div>
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

		$(document).on('keyup', '#cc_persona', function (event) {
			event.preventDefault();

			var codigo_tecla_presionada = event.which || event.keyCode;

			switch (codigo_tecla_presionada) {
				case 13: // Al presionar Enter
					$('#nombre_persona').select();
					break;
				default :
					break;
			}

		});

		$(document).on('keyup', '#nombre_persona', function (event) {
			event.preventDefault();

			var codigo_tecla_presionada = event.which || event.keyCode;

			switch (codigo_tecla_presionada) {
				case 13: // Al presionar Enter

					$('#btn_agregar_usuario').focus();

					break;
				default :
					break;
			}

		});

	$(document).on('click', '.delete', function(event) {
		event.preventDefault();
		$(this).closest('tr').remove();
	});

	$(document).on('click', '#btn_agregar_usuario', function(event) {
		event.preventDefault();
		$(this).off(event);
		$(this).attr('disabled','disabled');
		$('#form_agregar_usuario').submit();
	});

	function addRow(tabla) {
		var html = "<tr><td><input autocomplete='off' style='margin: 10px; border-right: 2px gray solid; border-bottom: 2px gray solid; width: 80%; background-color: #ddd;' type='text' class='form-control' name='identificacion[]' id='cc_persona' required /></td><td><input autocomplete='off' style='margin: 10px; border-right: 2px gray solid; border-bottom: 2px gray solid; width: 80%; background-color: #ddd;' type='text' class='form-control' name='persona[]' id='nombre_persona' required /></td><td><a href='#' class='btn btn-success' id='btn_agregar_usuario' title='Agregar'><i class='fa fa-check'></i></a></td></tr>";
		$('#' + tabla + ' tbody:last').after(html);
		$('#cc_persona').select();
	}
</script>
@endsection