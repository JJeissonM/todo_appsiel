<?php
	$records_list = App\Salud\DiagnosticoCie::where( [
											['consulta_id', '=', $consulta->id]
										] )
									->get();
	//dd( $data );
?>

<br>

<div id="tabla_registros_diagnostico_cie_{{$consulta->id}}">
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Diagnóstico principal hello</th>
				<th>Código CIE</th>
				<th>Tipo de diagnóstico</th>
				<th>Observaciones</th>
				<th>Acción</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $records_list AS $record )
				<?php
					//dd( $record->get_fields_to_show() );
				?>
				<tr>
					<td> {{ $record->get_fields_to_show()->es_diagnostico_principal->value }} </td>
					<td> {{ $record->get_fields_to_show()->codigo_cie->value }} </td>
					<td> {{ $record->get_fields_to_show()->tipo_diagnostico_principal->value }} </td>
					<td> {{ $record->get_fields_to_show()->observaciones->value }} </td>
					<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_registro_diagnostico_cie' data-consulta_id="{{ $consulta->id }}" data-paciente_id="{{ $record->get_fields_to_show()->paciente_id->value }}" data-id="{{ $record->get_fields_to_show()->id }}"><i class='glyphicon glyphicon-trash'></i></button> </td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
            <tr>
                <td colspan="5">
                    <button style="background-color: transparent; color: #3394FF; border: none;" class="btn_nuevo_registro_diagnostico_cie">
                    	<i class="fa fa-btn fa-plus"></i> Agregar registro
                    	<span data-consulta_id="{{ $consulta->id }}"></span>
                    </button>
                </td>
            </tr>
        </tfoot>
	</table>

	@include('consultorio_medico.diagnostico_cie.modal', [ 'consulta_id' => $consulta->id ])

</div>

@section('scripts9')

	<script type="text/javascript">
	

		$(document).ready(function(){
		console.log('hello');

			$(".btn_nuevo_registro_diagnostico_cie").click(function(event){

				event.preventDefault();
		        console.log('entra');
				
		        var consulta_id = $(this).children('span').attr('data-consulta_id');
				
		        $( '#modal_diagnostico_cie_' + consulta_id ).modal({backdrop: "static"});

		        $("#div_cargando").show();
		        
		        var modelo_id = 309;

		        var url = "{{ url('salud_diagnostico_cie/create') }}" + "?id_modelo=" + modelo_id + "&paciente_id=" + paciente_id + "&consulta_id=" + consulta_id;

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_diagnostico_cie_' + consulta_id).html(data);
					cargarCombobox()
				});	
					        
		    });

			$(document).on('click', '.btn_eliminar_registro_diagnostico_cie', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");

				if ( confirm('¿Desea eliminar el registro de diagnostico ' + fila.find('td').eq(1).html() ) )
				{
					$('#div_cargando').show();
	            	var url = "{{ url('salud_diagnostico_cie/delete') }}" + "/" + $(this).attr('data-id');

					$.get( url )
						.done(function( respuesta ) {
							$('#div_cargando').hide();
							fila.remove();
						});
				}
			});


			// GUARDAR 
			$(document).on("click",".btn_save_modal_diagnostico_cie",function(event){

		    	event.preventDefault();
		        
		        $(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );

		        var consulta_id = $(this).children('span').attr('data-consulta_id');
		        formulario = $('#modal_diagnostico_cie_' + consulta_id).find('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();

		        $.post(url, data, function (respuesta) {

		        	var fila = '<tr id="ultima_fila" style="display:none;"> <td> ' + respuesta.es_diagnostico_principal.value + ' </td> <td> ' + respuesta.codigo_cie.value + ' </td> <td> ' + respuesta.tipo_diagnostico_principal.value + ' </td> <td> ' + respuesta.observaciones.value + ' </td> <td> <button type="button" class="btn btn-danger btn-xs btn_eliminar_registro_diagnostico_cie" data-consulta_id="' + respuesta.consulta_id.value + '" data-paciente_id="' + respuesta.paciente_id.value + '" data-id="' + respuesta.id + '"><i class="glyphicon glyphicon-trash"></i></button> </td> </tr>';

		        	$('#tabla_registros_diagnostico_cie_' + consulta_id).find('tbody:last').append( fila );

					$('#ultima_fila').fadeIn(1000);
					$('#ultima_fila').removeAttr('id');

		        	$('#modal_diagnostico_cie_' + consulta_id).modal('hide');
		            $('#contenido_modal_diagnostico_cie_' + consulta_id).html('');

			        $('#modal_diagnostico_cie_' + consulta_id).find('.btn_save_modal_diagnostico_cie').children('.fa-spinner').attr('class','fa fa-save');
			        $('#modal_diagnostico_cie_' + consulta_id).find('.btn_save_modal_diagnostico_cie').removeAttr( 'disabled' );
		        });
		    });

		});
	</script>
@endsection