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
								$SMMLV = 877803; // Valor año 2020 
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
								$horas_laborales = 8; 
								if( isset($parametros['horas_laborales'] ) )
								{
									$horas_laborales = $parametros['horas_laborales'];
								}
							?>
							{{ Form::bsText('horas_laborales', $horas_laborales, 'Cantidad horas laborales', ['class'=>'form-control']) }}
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
							&nbsp;
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




	<div id="div_cargando">Cargando...</div>
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