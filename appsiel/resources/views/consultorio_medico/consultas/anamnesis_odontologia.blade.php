<?php
	use App\Http\Controllers\Core\ModeloEavController;

	$id_modelo = 95; // Pacientes
	
    $modelo_padre_id = 96; // Consultas Médicas
    $registro_modelo_padre_id = $consulta->id;

    $ids_modelos_relacionados = [ 304, 305, 306, 307 ];//, 308

?>

<br><br>

<ul class="nav nav-tabs">
	<?php $cont = 1; ?>
	@foreach($ids_modelos_relacionados AS $key => $value)
		<?php 
			$href = "#tab_" . $consulta->id . "_model_".$key;
			$modelo_entidad = App\Sistema\Modelo::find( $value );

			$class_active = '';
			if($cont == 1)
			{
				$class_active = 'active';
			}
		?>

		<li class="{{$class_active}}">
			<a data-toggle="tab" href="{{$href}}">{{ $modelo_entidad->descripcion }}</a>
		</li>
		<?php $cont++; ?>
	@endforeach
</ul>

<div class="tab-content">
	<br>
	<?php $cont = 1; ?>
	@foreach($ids_modelos_relacionados AS $key => $value)
		<?php 
			$ID = "tab_" . $consulta->id . "_model_".$key;
			$modelo_entidad_id = $value;
			$modelo_entidad = App\Sistema\Modelo::find( $value );

			$datos = ModeloEavController::show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_id );

			$class_active = '';
			if($cont == 1)
			{
				$class_active = 'active';
			}
		?>

		<div id="{{$ID}}" class="tab-pane fade in {{$class_active}}">
	        &nbsp;&nbsp;&nbsp;
	        <button class="btn btn-warning btn-xs btn_actualizar_modelo" data-url="{{ url( 'core/eav/' . $modelo_entidad_id . '/edit?id=' . Input::get('id') . '&id_modelo=' . $modelo_entidad_id . '&modelo_padre_id=' . $modelo_padre_id . '&registro_modelo_padre_id=' . $registro_modelo_padre_id . '&modelo_entidad_id=' . $modelo_entidad_id . '&modo_peticion=ajax' ) }}" title="Actualizar" data-panel_id="{{$ID}}"><i class="fa fa-btn fa-edit"></i></button>

			@include( 'core.modelo_eav.form_eliminar_registros', [ 'id_app' => Input::get('id'), 'id_modelo' => Input::get('id_modelo'), 'modelo_padre_id' => $modelo_padre_id, 'registro_modelo_padre_id' => $registro_modelo_padre_id, 'modelo_entidad_id' => $modelo_entidad_id, 'lbl_descripcion_modelo_entidad' => $modelo_entidad->descripcion, 'ruta_redirect' => 'consultorio_medico/pacientes/'.$registro->id ] )

			<br><br>

			<div class="div_spin" style="width: 100%; display: none; text-align: center;">
			    <img src="{{ asset( 'img/spinning-wheel.gif') }}" width="64px">
			</div>
    		
    		<div class="alert alert-success alert-dismissible fade in" style="display: none;" id="mensaje_alerta">
    		</div>
			
			<div id="contenido_seccion_modelo_{{$ID}}" class="contenido_seccion_modelo">
				{!! $datos !!}
			</div>

        </div>

		<?php $cont++; ?>
	@endforeach
</div>

<br>
<br>

@section('scripts3')
	<script>
		
		var continuar = true;
		var url, panel_id, div_contenido_seccion_modelo;

		$(document).ready(function(){

			$(".btn_actualizar_modelo").click(function(event){

		        panel_id = $(this).attr('data-panel_id');
				
				$( "#" + panel_id + " .div_spin").show();
		        
				$('#contenido_seccion_modelo_' + panel_id ).html( '' );

        		$( "#" + panel_id + " .close").hide();
		        $( "#" + panel_id + ".btn_close_modal").hide();
		        $( "#" + panel_id + ".btn_save_modal").hide();

		        url = $(this).attr('data-url');

		        cargar_formulario().then( function() {

		        	if ( !continuar )
					{
		        		$( panel_id + ".btn_close_modal").show();
						return 0;
					}else{
					    $( "#" + panel_id + " .div_spin").hide();;
					}

				}, function( error ) { //, data, textStatus, xhr
				    $('#contenido_seccion_modelo_' + panel_id ).html( error );
		        	$( "#" + panel_id + ".btn_close_modal").fadeIn(1000);
				});
		    });

		    $(document).on("click",".btn_save_modal",function(event){

		    	event.preventDefault();

		        panel_id = $(this).parent('div').parent('div').parent('div').parent('div').attr('id');
				
				$( "#" + panel_id + " .div_spin").show();
		        
				$('#contenido_seccion_modelo_' + panel_id ).html( '' );
		        
		        formulario = $(this).prev('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();
				
				console.log([panel_id,formulario,url]);

		        $.post(url, data, function (datos) {
		        	$( "#" + panel_id).find('.div_spin').hide();
					$('#contenido_seccion_modelo_' + panel_id ).html( datos );
		        });
		    });

		    $(document).on("click",".btn_close_modal",function(event){

		    	event.preventDefault();

		        panel_id = $(this).parent('div').parent('div').parent('div').parent('div').attr('id');
				
				$( "#" + panel_id + " .div_spin").show();
		        
				$('#contenido_seccion_modelo_' + panel_id ).html( '' );
		        
		        formulario = $(this).prev().prev('form');

		        var url = formulario.attr('action').replace( 'core_eav_update_db', 'core_eav_cancelar_update_db' );
		        var data = formulario.serialize();

		        $.post(url, data, function (datos) {
		        	$( "#" + panel_id).find('.div_spin').hide();
					$('#contenido_seccion_modelo_' + panel_id ).html( datos );
		        });
		    });

		    function cargar_formulario()
			{
				return $.get( url ).then(function( data ) {
					if ( data == 1 ) // Cuando falla la validacion.
					{
						continuar = false;
						$( '#contenido_seccion_modelo_' + panel_id ).html( '<h1 style="text-align:center;"> <small>Por favor intente nuevamente.</small>  <br> Falló la carga del formulario. <i class="fa fa-remove"></i> </h1>' + data );
					}else{
						continuar = true;
						$( '#contenido_seccion_modelo_' + panel_id ).html( data );
					}

						
			    }, function( data, textStatus, xhr ) {
			        return '<h1 style="text-align:center;">  <small style="color:red;"> <i class="fa fa-times-circle"></i> Falló la carga del formulario. </small> <br> Code: ' + data.status + '  <br> Status: ' + textStatus + " - " + xhr + ' </h1>';
			    });
			}

		});
	</script>
@endsection