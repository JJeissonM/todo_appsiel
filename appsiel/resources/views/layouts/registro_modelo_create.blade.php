<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

<div class="container-fluid">
	<div class="marco_formulario">
	    <h4 style="width: 100%; text-align: center;">Nuevo registro: {{ $modelo->descripcion }}</h4>
	    <hr>

		{{ Form::open(['url'=>$form_create['url'],'id'=>'form_create','files' => true]) }}

			<?php
				$botones = "El modelo no tiene campos asociados.";
			  	if ( count($form_create['campos'])>0 ) {
			  		$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
			  		$botones = Form::bsButtonsForm($url);
			  	}
			?>

			<div class="row botones" style="margin: 5px;"> {{ $botones }} </div>

			{{ VistaController::campos_una_colummna($form_create['campos']) }}

			{{ Form::hidden('url_id',Input::get('id')) }}
			{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
			{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}
			
		{{ Form::close() }}

	</div>
</div>
<br/><br/>

<script type="text/javascript">
	$(document).ready(function(){

		$('#fecha').val( get_fecha_hoy() );

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
@if( isset($archivo_js) )
	<script src="{{ asset( $archivo_js ) }}"></script>
@endif
