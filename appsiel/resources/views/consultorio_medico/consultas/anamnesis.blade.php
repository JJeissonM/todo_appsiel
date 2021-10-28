<?php
	use App\Http\Controllers\Core\ModeloEavController;
	
    $modelo_padre_id = 96; // Consultas Médicas
    $registro_modelo_padre_id = $consulta->id;
	$modelo_entidad_id = 110; // Anamnesis

	$modelo_entidad = App\Sistema\Modelo::find( $modelo_entidad_id );
	
	$datos = ModeloEavController::show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_id );

?>
<br>

<h4>{{$modelo_entidad->descripcion}}</h4>
<hr>

@can('salud_anamnesis_edit')
	<button class="btn-gmail btn_edit_registro_anamnesis" title="Modificar" data-url="{{ url( 'core/eav/' . $modelo_entidad_id . '/edit?id=' . Input::get('id') . '&id_modelo=' . $modelo_entidad_id . '&modelo_padre_id=' . $modelo_padre_id . '&registro_modelo_padre_id=' . $registro_modelo_padre_id . '&modelo_entidad_id=' . $modelo_entidad_id . '&modo_peticion=ajax&buttons=no' ) }}">
		<i class="fa fa-btn fa-edit"></i>
        <span data-consulta_id="{{ $consulta->id }}"></span>
    </button>
@endcan

@include( 'core.modelo_eav.form_eliminar_registros', [ 'id_app' => Input::get('id'), 'id_modelo' => Input::get('id_modelo'), 'modelo_padre_id' => $modelo_padre_id, 'registro_modelo_padre_id' => $registro_modelo_padre_id, 'modelo_entidad_id' => $modelo_entidad_id, 'lbl_descripcion_modelo_entidad' => $modelo_entidad->descripcion, 'ruta_redirect' => 'consultorio_medico/pacientes/'.$registro->id, 'btn_gmail' => 1 ] )

<br><br>
<div id="tabla_anamnesis_{{$consulta->id}}">
	{!! $datos !!}
</div>


@include('consultorio_medico.consultas.anamnesis_modal', [ 'consulta_id' => $consulta->id ])

@section('scripts14')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_edit_registro_anamnesis").click(function(event){

				event.preventDefault();
		        
		        var consulta_id = $(this).children('span').attr('data-consulta_id');
				
		        $( '#modal_anamnesis_' + consulta_id ).modal({backdrop: "static"});

		        console.log( $( '#modal_anamnesis_' + consulta_id ).find('.btn_save_modal') );

		        $( '.btn_save_modal' ).hide();
		        $( '#modal_anamnesis_' + consulta_id ).find('form').next().next().hide();

		        $("#div_cargando").show();

		        var url = $(this).attr('data-url');

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_anamnesis_' + consulta_id).html(data);
				});		        
		    });


			// GUARDAR 
			$(document).on("click",".btn_save_modal_anamnesis",function(event){

		    	event.preventDefault();
		        
		        $(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );

		        var consulta_id = $(this).children('span').attr('data-consulta_id');
		        formulario = $('#modal_anamnesis_' + consulta_id).find('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();

		        $.post(url, data, function (respuesta) {

		        	$('#modal_anamnesis_' + consulta_id).modal('hide');
		            $('#contenido_modal_anamnesis_' + consulta_id).html('');

			        $('#modal_anamnesis_' + consulta_id).find('.btn_save_modal_anamnesis').children('.fa-spinner').attr('class','fa fa-save');
			        $('#modal_anamnesis_' + consulta_id).find('.btn_save_modal_anamnesis').removeAttr( 'disabled' );

		        	$('#tabla_anamnesis_' + consulta_id).html('');
		        	$('#tabla_anamnesis_' + consulta_id).hide();
		        	$('#tabla_anamnesis_' + consulta_id).html( '<span style="color: green;"><i class="fa fa-check"></i> Registro actualizado correctamente</span><br>' + respuesta);
		        	$('#tabla_anamnesis_' + consulta_id).fadeIn(1000);

		        });
		    });

		});
	</script>
@endsection