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



@section('scripts10')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_nuevo_registro_examen").click(function(event){

				event.preventDefault();
		        
		        var consulta_id = $(this).children('span').attr('data-consulta_id');
				
		        $( '#modal_examen_' + consulta_id ).modal({backdrop: "static"});

		        $("#div_cargando").show();
		        
		        var modelo_id = 308;

		        var url = "{{ url('salud_examen/create') }}" + "?id_modelo=" + modelo_id + "&paciente_id=" + paciente_id + "&consulta_id=" + consulta_id;

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_examen_' + consulta_id).html(data);
				});		        
		    });

			$(document).on('click', '.btn_eliminar_registro_examen', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");

				if ( confirm('¿Desea eliminar el registro de examen para el diente # ' + fila.find('td').eq(0).html() ) )
				{
					$('#div_cargando').show();
	            	var url = "{{ url('salud_examen/delete') }}" + "/" + $(this).attr('data-id');

					$.get( url )
						.done(function( respuesta ) {
							$('#div_cargando').hide();
							fila.remove();
						});
				}
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

		        	var fila = '<tr id="ultima_fila" style="display:none;"> <td> ' + respuesta.numero_diente.value + ' </td> <td> ' + respuesta.frio.value + ' </td> <td> ' + respuesta.caliente.value + ' </td> <td> ' + respuesta.percusion_horizontal.value + ' </td> <td> ' + respuesta.percusion_vertical.value + ' </td> <td> ' + respuesta.observaciones.value + ' </td> <td> <button type="button" class="btn btn-danger btn-xs btn_eliminar_registro_examen" data-consulta_id="' + respuesta.consulta_id.value + '" data-paciente_id="' + respuesta.paciente_id.value + '" data-id="' + respuesta.id + '"><i class="glyphicon glyphicon-trash"></i></button> </td> </tr>';

		        	$('#tabla_registros_examen_' + consulta_id).find('tbody:last').append( fila );

					$('#ultima_fila').fadeIn(1000);
					$('#ultima_fila').removeAttr('id');

		        	$('#modal_examen_' + consulta_id).modal('hide');
		            $('#contenido_modal_examen_' + consulta_id).html('');

			        $('#modal_examen_' + consulta_id).find('.btn_save_modal_examen').children('.fa-spinner').attr('class','fa fa-save');
			        $('#modal_examen_' + consulta_id).find('.btn_save_modal_examen').removeAttr( 'disabled' );
		        });
		    });

		});
	</script>
@endsection