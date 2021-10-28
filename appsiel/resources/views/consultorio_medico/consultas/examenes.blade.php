<?php
	$examenes = App\Salud\ExamenMedico::examenes_del_paciente( $consulta->paciente_id, $consulta->id );
	$cantidad = count( $examenes );
?>

<br>
<!-- Este for dibuja un botón en cada iteración -->
<div class="btns_examenes">
	@for($i = 0; $i < $cantidad; $i++ )
		{!! $examenes[$i] !!}
	@endfor
</div>

@include('consultorio_medico.consultas.examenes_modal', [ 'consulta_id' => $consulta->id ])

@include('consultorio_medico.consultas.examenes_modal_update', [ 'consulta_id' => $consulta->id ])

@section('scripts10')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_create_examen").click(function(event){

				event.preventDefault();
		        
		        var consulta_id = $(this).children('span').attr('data-consulta_id');
				
		        $( '#modal_examen_' + consulta_id ).modal({backdrop: "static"});

		        $("#div_cargando").show();

		        var url = $(this).attr('data-url');

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_examen_' + consulta_id).html(data);
				});		        
		    });


			// GUARDAR 
			$(document).on("click",".btn_save_modal_examen",function(event){

		    	event.preventDefault();
		        
		        $(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );

		        var consulta_id = $(this).children('span').attr('data-consulta_id');
		        formulario = $('#modal_examen_' + consulta_id).find('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();

		        $.post(url, data, function (respuesta) {

		        	$( '#modal_examen_' + consulta_id).modal('hide');
		            $( '#contenido_modal_examen_' + consulta_id).html('');

			        $( '#modal_examen_' + consulta_id).find('.btn_save_modal_examen').children('.fa-spinner').attr('class','fa fa-save');
			        $( '#modal_examen_' + consulta_id).find('.btn_save_modal_examen').removeAttr( 'disabled' );

			        var button_id = '#btn_create_examen_' + respuesta.consulta_id + '_' + respuesta.examen_id;

			        $( button_id ).attr('class','btn btn-default btn-xs btn_ver_examen');
			        $( button_id ).children( 'i' ).attr('class','fa fa-eye');
			        $( button_id ).children( 'span' ).remove();
			        $( button_id ).removeAttr('data-url');
			        $( button_id ).removeAttr('id');

		        });
		    });
			
			$(document).on( "click", ".btn_ver_examen", function(event){
				event.preventDefault();

				var consulta_id = $(this).attr('data-consulta_id');
				$( '#alert_mensaje_' + consulta_id).hide();

				$( '#info_examen_' + consulta_id).html( '' );
				$('#div_cargando').fadeIn();
		        
		        $('#modal_examen_update_' + consulta_id).modal(
		        	{keyboard: 'true'}
		        );

		        var url = '../../consultorio_medico/resultado_examen_medico/' + $(this).attr('data-consulta_id') + '-' + $(this).attr('data-paciente_id') + '-' + $(this).attr('data-examen_id') + '?id='+getParameterByName('id') + '&id_modelo='+getParameterByName('id_modelo');

		        $(".btn_edit_examen").attr('data-consulta_id' , $(this).attr('data-consulta_id') );
		        $(".btn_edit_examen").attr('data-paciente_id', $(this).attr('data-paciente_id') );
		        $(".btn_edit_examen").attr('data-examen_id', $(this).attr('data-examen_id') );

		        //console.log( $(this).html() );

		        $(".modal-title").html( $(this).html() );

		        $.get( url, function( respuesta ){
		        	$( '#div_cargando_' + consulta_id).hide();
		        	$( '#info_examen_' + consulta_id).html( respuesta );
		        });/**/
		    });

			
			$(".btn_edit_examen").click(function(event){
				event.preventDefault();

				$("#alert_mensaje").hide();

				$('.modal-body .campo_variable').each(function()
				{

				    var cadena = $.trim( $(this).text() );

				    //console.log( cadena );

				    $(this).html( $('<input/>').attr({ type: 'text', value: cadena, name: 'campo_variable_organo-' + $(this).attr('id'), class: 'form-control', size: '5' }) );

				});

				$(this).hide();
				$(".btn_save_examen").show();
		    });
			
			$(".btn_save_examen").click(function(event){
				event.preventDefault();

				$('#div_cargando').show();

				var form = $('.modal-body #form_resultados_examenes');
				var url = form.attr('action');
				data = form.serialize();
				$.post(url,data,function(result){

					//alert( result );

					$('.modal-body .campo_variable').each(function()
					{
					    var cadena = $(this).children('input').val();
					    $(this).html( cadena );
					});

					$('#div_cargando').hide();
					
					$(".btn_save_examen").hide();
					$(".btn_edit_examen").show();

					$("#alert_mensaje").show();
				});					
		    });

		    /*$('#modal_examen_update_' + consulta_id).on('hidden.bs.modal', function(){
			    $(".btn_save_examen").hide();
			    $(".btn_edit_examen").show();
			});*/

		});
	</script>
@endsection