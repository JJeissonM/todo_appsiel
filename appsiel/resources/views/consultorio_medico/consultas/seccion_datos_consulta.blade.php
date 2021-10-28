<br><br>

@include('consultorio_medico.pacientes_show_botones_accion')

<div id="tabla_datos_consulta_{{$consulta->id}}">
	@include( 'consultorio_medico.consultas.datos_consulta' )
</div>

@include('consultorio_medico.consultas.modal', [ 'consulta_id' => $consulta->id ])

@section('scripts13')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_edit_registro_datos_consulta").click(function(event){

				event.preventDefault();
		        
		        var consulta_id = $(this).children('span').attr('data-consulta_id');
				
		        $( '#modal_datos_consulta_' + consulta_id ).modal({backdrop: "static"});

		        $("#div_cargando").show();
		        
		        var modelo_id = 96; // Pacientes

		        var url = "{{ url('consultorio_medico/consultas') }}" + "/" + consulta_id + "/edit?id=18" + "&id_modelo=" + modelo_id + "&paciente_id=" + paciente_id + "&action=edit";

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_datos_consulta_' + consulta_id).html(data);
				});		        
		    });


			// GUARDAR 
			$(document).on("click",".btn_save_modal_datos_consulta",function(event){

		    	event.preventDefault();
		        
		        $(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );

		        var consulta_id = $(this).children('span').attr('data-consulta_id');
		        formulario = $('#modal_datos_consulta_' + consulta_id).find('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();

		        $.post(url, data, function (respuesta) {

		        	$('#modal_datos_consulta_' + consulta_id).modal('hide');
		            $('#contenido_modal_datos_consulta_' + consulta_id).html('');

			        $('#modal_datos_consulta_' + consulta_id).find('.btn_save_modal_datos_consulta').children('.fa-spinner').attr('class','fa fa-save');
			        $('#modal_datos_consulta_' + consulta_id).find('.btn_save_modal_datos_consulta').removeAttr( 'disabled' );

		        	$('#tabla_datos_consulta_' + consulta_id).html('');
		        	$('#tabla_datos_consulta_' + consulta_id).hide();
		        	$('#tabla_datos_consulta_' + consulta_id).html( '<span style="color: green;"><i class="fa fa-check"></i> Registro actualizado correctamente</span><br>' + respuesta);
		        	$('#tabla_datos_consulta_' + consulta_id).fadeIn(1000);

		        });
		    });

			$(".btn_delete_registro_datos_consulta").click(function(event){

				event.preventDefault();
		        
		        var consulta_id = $(this).children('span').attr('data-consulta_id');
				
		        if ( confirm( '¿Realmente desea eliminar el registro de la consulta No. ' + consulta_id + '?' ) )
		        {
		        	location = $(this).attr('data-url');
		        }	        
		    });

		});
	</script>
@endsection

