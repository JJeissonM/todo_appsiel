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
		    <h4>{!!$parametros['titulo']!!}</h4>
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

				<div class="row" style="margin: 5px;"> {{ Form::bsButtonsForm( url()->previous() ) }} </div>

				{{ Form::hidden('titulo', $parametros['titulo']) }}

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['inv_producto_id_default_matricula'] ) )
								{
									$inv_producto_id_default_matricula = $parametros['inv_producto_id_default_matricula'];
								}else{
									$inv_producto_id_default_matricula = 'Si';
								}
							?>
							{{ Form::bsSelect('inv_producto_id_default_matricula', $inv_producto_id_default_matricula, 'Concepto por defecto para Matrícula', App\Inventarios\Servicio::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['inv_producto_id_default_pension'] ) )
								{
									$inv_producto_id_default_pension = $parametros['inv_producto_id_default_pension'];
								}else{
									$inv_producto_id_default_pension = 'Si';
								}
							?>
							{{ Form::bsSelect('inv_producto_id_default_pension', $inv_producto_id_default_pension, 'Concepto por defecto para Pensión', App\Inventarios\Servicio::opciones_campo_select(), ['class'=>'form-control']) }}
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