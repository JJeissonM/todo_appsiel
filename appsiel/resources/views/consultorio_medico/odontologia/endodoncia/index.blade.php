<?php
	use App\Http\Controllers\Sistema\VistaController;

	$records_list = App\Salud\Endodoncia::where( [
											['consulta_id', '=', $consulta->id]
										] )
									->get();
	//dd( $data );
?>

<br>

<div id="tabla_registros_endodoncia_{{$consulta->id}}">
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th rowspan="2">Diente</th>
				<th colspan="2">Pruebas de sensibilidad a la pulpa dental</th>
				<th colspan="2">Pruebas de sensibilidad periodontal</th>
				<th rowspan="2">Observaciones</th>
				<th rowspan="2">Acción</th>
			</tr>
			<tr>
				<th>Frio</th>
				<th>Caliente</th>
				<th>Percusión horizontal</th>
				<th>Percusión vertical</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $records_list AS $record )
				<?php
					//dd( $record->get_fields_to_show() );
				?>
				<tr>
					<td> {{ $record->get_fields_to_show()->numero_diente->value }} </td>
					<td> {{ $record->get_fields_to_show()->frio->value }} </td>
					<td> {{ $record->get_fields_to_show()->caliente->value }} </td>
					<td> {{ $record->get_fields_to_show()->percusion_horizontal->value }} </td>
					<td> {{ $record->get_fields_to_show()->percusion_vertical->value }} </td>
					<td> {{ $record->get_fields_to_show()->observaciones->value }} </td>
					<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_registro_endodoncia' data-consulta_id="{{ $consulta->id }}" data-paciente_id="{{ $record->get_fields_to_show()->paciente_id->value }}" data-id="{{ $record->get_fields_to_show()->id }}"><i class='glyphicon glyphicon-trash'></i></button> </td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
            <tr>
                <td colspan="5">
                    <button style="background-color: transparent; color: #3394FF; border: none;" class="btn_nuevo_registro_endodoncia">
                    	<i class="fa fa-btn fa-plus"></i> Agregar registro
                    	<span data-consulta_id="{{ $consulta->id }}"></span>
                    </button>
                </td>
            </tr>
        </tfoot>
	</table>

	@include('consultorio_medico.odontologia.endodoncia.modal', [ 'consulta_id' => $consulta->id ])

</div>

@section('scripts8')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_nuevo_registro_endodoncia").click(function(event){

				event.preventDefault();
		        
		        var consulta_id = $(this).children('span').attr('data-consulta_id');
				
		        $( '#modal_endodoncia_' + consulta_id ).modal({backdrop: "static"});

		        $("#div_cargando").show();
		        
		        var modelo_id = 308;

		        var url = "{{ url('salud_endodoncia/create') }}" + "?id_modelo=" + modelo_id + "&paciente_id=" + paciente_id + "&consulta_id=" + consulta_id;

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_endodoncia_' + consulta_id).html(data);
				});		        
		    });

			$(document).on('click', '.btn_eliminar_registro_endodoncia', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");

				if ( confirm('¿Desea eliminar el registro de endodoncia para el diente # ' + fila.find('td').eq(0).html() ) )
				{
					$('#div_cargando').show();
	            	var url = "{{ url('salud_endodoncia/delete') }}" + "/" + $(this).attr('data-id');

					$.get( url )
						.done(function( respuesta ) {
							$('#div_cargando').hide();
							fila.remove();
						});
				}
			});


			// GUARDAR 
			$(document).on("click",".btn_save_modal_endodoncia",function(event){

		    	event.preventDefault();
		        
		        $(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );

		        var consulta_id = $(this).children('span').attr('data-consulta_id');
		        formulario = $('#modal_endodoncia_' + consulta_id).find('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();

		        $.post(url, data, function (respuesta) {

		        	var fila = '<tr id="ultima_fila" style="display:none;"> <td> ' + respuesta.numero_diente.value + ' </td> <td> ' + respuesta.frio.value + ' </td> <td> ' + respuesta.caliente.value + ' </td> <td> ' + respuesta.percusion_horizontal.value + ' </td> <td> ' + respuesta.percusion_vertical.value + ' </td> <td> ' + respuesta.observaciones.value + ' </td> <td> <button type="button" class="btn btn-danger btn-xs btn_eliminar_registro_endodoncia" data-consulta_id="' + respuesta.consulta_id.value + '" data-paciente_id="' + respuesta.paciente_id.value + '" data-id="' + respuesta.id + '"><i class="glyphicon glyphicon-trash"></i></button> </td> </tr>';

		        	$('#tabla_registros_endodoncia_' + consulta_id).find('tbody:last').append( fila );

					$('#ultima_fila').fadeIn(1000);
					$('#ultima_fila').removeAttr('id');

		        	$('#modal_endodoncia_' + consulta_id).modal('hide');
		            $('#contenido_modal_endodoncia_' + consulta_id).html('');

			        $('#modal_endodoncia_' + consulta_id).find('.btn_save_modal_endodoncia').children('.fa-spinner').attr('class','fa fa-save');
			        $('#modal_endodoncia_' + consulta_id).find('.btn_save_modal_endodoncia').removeAttr( 'disabled' );
		        });
		    });

		});
	</script>
@endsection