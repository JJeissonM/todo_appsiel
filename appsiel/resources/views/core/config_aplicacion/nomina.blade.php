@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		#div_cargando{
			display: none;/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom:0px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index:999;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>{!! $parametros['titulo'] !!}</h4>
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

				{{ Form::hidden('titulo', $parametros['titulo']) }}

				<h4> Parámetros generales  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$SMMLV = 908526; // Valor año 2021
								if( isset($parametros['SMMLV'] ) )
								{
									$SMMLV = $parametros['SMMLV'];
								}
							?>
							{{ Form::bsText('SMMLV', $SMMLV, 'Salario Mínimo Mensual Legal Vigente', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$horas_laborales = 240; 
								if( isset($parametros['horas_laborales'] ) )
								{
									$horas_laborales = $parametros['horas_laborales'];
								}
							?>
							{{ Form::bsText('horas_laborales', $horas_laborales, 'Cantidad horas laborales mes', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$horas_dia_laboral = 8; 
								if( isset($parametros['horas_dia_laboral'] ) )
								{
									$horas_dia_laboral = $parametros['horas_dia_laboral'];
								}
							?>
							{{ Form::bsText('horas_dia_laboral', $horas_dia_laboral, 'Horas días laboral', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$valor_uvt_actual = 36308; // Año 2021 
								if( isset($parametros['valor_uvt_actual'] ) )
								{
									$valor_uvt_actual = $parametros['valor_uvt_actual'];
								}
							?>
							{{ Form::bsText('valor_uvt_actual', $valor_uvt_actual, 'Valor UVT año actual', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$sabado_es_dia_habil = 1; 
								if( isset($parametros['sabado_es_dia_habil'] ) )
								{
									$sabado_es_dia_habil = $parametros['sabado_es_dia_habil'];
								}
							?>
							{{ Form::bsSelect('sabado_es_dia_habil', $sabado_es_dia_habil, 'El sábado es día hábil', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$calcular_valor_proyectado_fondo_solidaridad = 1; 
								if( isset($parametros['calcular_valor_proyectado_fondo_solidaridad'] ) )
								{
									$calcular_valor_proyectado_fondo_solidaridad = $parametros['calcular_valor_proyectado_fondo_solidaridad'];
								}
							?>
							{{ Form::bsSelect('calcular_valor_proyectado_fondo_solidaridad', $calcular_valor_proyectado_fondo_solidaridad, 'Calcula valor proyectado para fondo de solidaridad pensional', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$concepto_salario_integral = 2;
								if( isset($parametros['concepto_salario_integral'] ) )
								{
									$concepto_salario_integral = $parametros['concepto_salario_integral'];
								}
							?>
							{{ Form::bsSelect('concepto_salario_integral', $concepto_salario_integral, 'Concepto de salario integral', App\Nomina\NomConcepto::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$concepto_salud_obligatoria = 64;
								if( isset($parametros['concepto_salud_obligatoria'] ) )
								{
									$concepto_salud_obligatoria = $parametros['concepto_salud_obligatoria'];
								}
							?>
							{{ Form::bsSelect('concepto_salud_obligatoria', $concepto_salud_obligatoria, 'Concepto Salud Obligatoria', App\Nomina\NomConcepto::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$pago_salario_completo_en_incapacidades = 1; 
								if( isset($parametros['pago_salario_completo_en_incapacidades'] ) )
								{
									$pago_salario_completo_en_incapacidades = $parametros['pago_salario_completo_en_incapacidades'];
								}
							?>
							{{ Form::bsSelect('pago_salario_completo_en_incapacidades', $pago_salario_completo_en_incapacidades, 'Pago de salario completo en incapacidades', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$id_concepto_pagar_empresa_en_incapacidades = 2;
								if( isset($parametros['id_concepto_pagar_empresa_en_incapacidades'] ) )
								{
									$id_concepto_pagar_empresa_en_incapacidades = $parametros['id_concepto_pagar_empresa_en_incapacidades'];
								}
							?>
							{{ Form::bsSelect('id_concepto_pagar_empresa_en_incapacidades', $id_concepto_pagar_empresa_en_incapacidades, 'Concepto incapacidad asumida por la empresa', App\Nomina\NomConcepto::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros liquidación de prestaciones sociales  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$concepto_vacaciones_dias_no_habiles = 83;
								if( isset($parametros['concepto_vacaciones_dias_no_habiles'] ) )
								{
									$concepto_vacaciones_dias_no_habiles = $parametros['concepto_vacaciones_dias_no_habiles'];
								}
							?>
							{{ Form::bsSelect('concepto_vacaciones_dias_no_habiles', $concepto_vacaciones_dias_no_habiles, 'Concepto a pagar para días NO hábiles de vacaciones', App\Nomina\NomConcepto::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$dias_calendario_por_dia_vacacion_legal = 24.35; 
								if( isset($parametros['dias_calendario_por_dia_vacacion_legal'] ) )
								{
									$dias_calendario_por_dia_vacacion_legal = $parametros['dias_calendario_por_dia_vacacion_legal'];
								}
							?>
							{{ Form::bsText('dias_calendario_por_dia_vacacion_legal', $dias_calendario_por_dia_vacacion_legal, 'Días calendario por cada día de vacación legal', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros generación Plaanilla integrada Autoliquidación de Aportes (PILA)  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$agrupacion_calculo_ibc_salud = 1;
								if( isset($parametros['agrupacion_calculo_ibc_salud'] ) )
								{
									$agrupacion_calculo_ibc_salud = $parametros['agrupacion_calculo_ibc_salud'];
								}
							?>
							{{ Form::bsSelect('agrupacion_calculo_ibc_salud', $agrupacion_calculo_ibc_salud, 'Agrupación para calcular IBC Salud, Pensión y Riesgos laborales', App\Nomina\AgrupacionConcepto::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$agrupacion_calculo_ibc_parafiscales = 7;
								if( isset($parametros['agrupacion_calculo_ibc_parafiscales'] ) )
								{
									$agrupacion_calculo_ibc_parafiscales = $parametros['agrupacion_calculo_ibc_parafiscales'];
								}
							?>
							{{ Form::bsSelect('agrupacion_calculo_ibc_parafiscales', $agrupacion_calculo_ibc_parafiscales, 'Agrupación para calcular IBC Parafiscales', App\Nomina\AgrupacionConcepto::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros liquidación de aprendices  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$concepto_a_pagar_pasante_sena = 75;
								if( isset($parametros['concepto_a_pagar_pasante_sena'] ) )
								{
									$concepto_a_pagar_pasante_sena = $parametros['concepto_a_pagar_pasante_sena'];
								}
							?>
							{{ Form::bsSelect('concepto_a_pagar_pasante_sena', $concepto_a_pagar_pasante_sena, 'Concepto a pagar', App\Nomina\NomConcepto::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$porcentaje_liquidacon_pasante_sena = 75; 
								if( isset($parametros['porcentaje_liquidacon_pasante_sena'] ) )
								{
									$porcentaje_liquidacon_pasante_sena = $parametros['porcentaje_liquidacon_pasante_sena'];
								}
							?>
							{{ Form::bsText('porcentaje_liquidacon_pasante_sena', $porcentaje_liquidacon_pasante_sena, 'Porcentaje del sueldo básico a pagar', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros de contabilización  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cuenta_id_salarios_por_pagar = 206;
								if( isset($parametros['cuenta_id_salarios_por_pagar'] ) )
								{
									$cuenta_id_salarios_por_pagar = $parametros['cuenta_id_salarios_por_pagar'];
								}
							?>
							{{ Form::bsSelect('cuenta_id_salarios_por_pagar', $cuenta_id_salarios_por_pagar, 'Cuenta por defecto de salarios por pagar', App\Contabilidad\ContabCuenta::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tercero_id_salarios_por_pagar = '';
								if( isset($parametros['tercero_id_salarios_por_pagar'] ) )
								{
									$tercero_id_salarios_por_pagar = $parametros['tercero_id_salarios_por_pagar'];
								}
							?>
							{{ Form::bsSelect('tercero_id_salarios_por_pagar', $tercero_id_salarios_por_pagar, 'Tercero por defecto de salarios por pagar', App\Core\Tercero::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros de Nómina Electrónica  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$proveedor_tecnologico_default = 'DATAICO';
								if( isset($parametros['proveedor_tecnologico_default'] ) )
								{
									$proveedor_tecnologico_default = $parametros['proveedor_tecnologico_default'];
								}
							?>
							{{ Form::bsSelect('proveedor_tecnologico_default', $proveedor_tecnologico_default, 'Proveedor tecnológico', ['DATAICO' => 'DATAICO', 'TFHKA' => 'The Fatory HKA'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$nom_elec_ambiente = 'PRUEBAS';
								if( isset($parametros['nom_elec_ambiente'] ) )
								{
									$nom_elec_ambiente = $parametros['nom_elec_ambiente'];
								}
							?>
							{{ Form::bsSelect('nom_elec_ambiente', $nom_elec_ambiente, 'Ambiente', ['PRUEBAS' => 'PRUEBAS', 'PRODUCCION' => 'PRODUCCION'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$pin_software = 1234567;
								if( isset($parametros['pin_software'] ) )
								{
									$pin_software = $parametros['pin_software'];
								}
							?>
							{{ Form::bsText('pin_software', $pin_software, 'PIN del software del prov. tec.', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$enviar_email_empleados = 'false';
								if( isset($parametros['enviar_email_empleados'] ) )
								{
									$enviar_email_empleados = $parametros['enviar_email_empleados'];
								}
							?>
							{{ Form::bsSelect('enviar_email_empleados', $enviar_email_empleados, 'Enviar facturas por email al empleado', ['false' => 'No', 'true' => 'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$email_copia_documento_soporte = '';
								if( isset($parametros['email_copia_documento_soporte'] ) )
								{
									$email_copia_documento_soporte = $parametros['email_copia_documento_soporte'];
								}
							?>
							{{ Form::bsText('email_copia_documento_soporte', $email_copia_documento_soporte, 'Enviar copia del doc. soporte nómina del empleado a este E-mail', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$url_servicio_emision = '';
								if( isset($parametros['url_servicio_emision'] ) )
								{
									$url_servicio_emision = $parametros['url_servicio_emision'];
								}
							?>
							{{ Form::bsText('url_servicio_emision', $url_servicio_emision, 'URL Servicio Emisión documento nómina', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$url_nota_ajuste_reemplazo = '';
								if( isset($parametros['url_nota_ajuste_reemplazo'] ) )
								{
									$url_nota_ajuste_reemplazo = $parametros['url_nota_ajuste_reemplazo'];
								}
							?>
							{{ Form::bsText('url_nota_ajuste_reemplazo', $url_nota_ajuste_reemplazo, 'URL Servicio Emisión Nota de Ajuste (Reemplazo) Nómina Electrónica', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>


				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$url_nota_ajuste_eliminacion = '';
								if( isset($parametros['url_nota_ajuste_eliminacion'] ) )
								{
									$url_nota_ajuste_eliminacion = $parametros['url_nota_ajuste_eliminacion'];
								}
							?>
							{{ Form::bsText('url_nota_ajuste_eliminacion', $url_nota_ajuste_eliminacion, 'URL Servicio Emisión Nota de Ajuste (Eliminación) Nómina Electrónica', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$tokenEmpresa = 'test-set-id';
								if( isset($parametros['tokenEmpresa'] ) )
								{
									$tokenEmpresa = $parametros['tokenEmpresa'];
								}
							?>
							{{ Form::bsText('tokenEmpresa', $tokenEmpresa, 'Token Empresa (test-set-id)', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$tokenDian = '';
								if( isset($parametros['tokenDian'] ) )
								{
									$tokenDian = $parametros['tokenDian'];
								}
							?>
							{{ Form::bsText('tokenDian', $tokenDian, 'Token DIAN (dian-id)', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$modalidad_asignada = '2';
								if( isset($parametros['modalidad_asignada'] ) )
								{
									$modalidad_asignada = $parametros['modalidad_asignada'];
								}
							?>
							{{ Form::bsSelect('modalidad_asignada', $modalidad_asignada, 'Modalidad asignada', ['1' => 'Automática', '2' => 'Manual Con Prefijo', '3' => 'Manual Sin Prefijo', '4' => 'Manual Contingencia'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<br><br>

				<div style="width: 100%; text-align: center;">
					<div class="row" style="margin: 5px;"> {{ Form::bsButtonsForm( url()->previous() ) }} </div>

					{{ Form::hidden('url_id',Input::get('id')) }}
					{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
				</div>
				
			{{ Form::close() }}
		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});

		});
	</script>
@endsection