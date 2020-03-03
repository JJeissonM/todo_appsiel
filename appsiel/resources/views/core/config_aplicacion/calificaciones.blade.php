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
		    <h4>{{$parametros['titulo']}}</h4>
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

				<div class="row" style="margin: 5px;"> {{ Form::bsButtonsForm( url()->previous() ) }} </div>

				{{ Form::hidden('titulo', $parametros['titulo']) }}

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['permitir_calificaciones_sin_logros'] ) )
								{
									$permitir_calificaciones_sin_logros = $parametros['permitir_calificaciones_sin_logros'];
								}else{
									$permitir_calificaciones_sin_logros = 'Si';
								}
							?>
							{{ Form::bsSelect('permitir_calificaciones_sin_logros', $permitir_calificaciones_sin_logros, 'Permitir ingreso de calificaciones sin haber ingresado logros', ['Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['manejar_preinformes_academicos'] ) )
								{
									$manejar_preinformes_academicos = $parametros['manejar_preinformes_academicos'];
								}else{
									$manejar_preinformes_academicos = 'No';
								}
							?>
							{{ Form::bsSelect('manejar_preinformes_academicos', $manejar_preinformes_academicos, 'Manejar pre-informes académicos', ['No'=>'No','Si'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['colegio_maneja_metas'] ) )
								{
									$colegio_maneja_metas = $parametros['colegio_maneja_metas'];
								}else{
									$colegio_maneja_metas = 'No';
								}
							?>
							{{ Form::bsSelect('colegio_maneja_metas', $colegio_maneja_metas, 'Manejar metas en boletines', ['Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
				
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