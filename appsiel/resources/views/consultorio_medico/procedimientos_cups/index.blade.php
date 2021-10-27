<?php
	use App\Http\Controllers\Sistema\VistaController;

	$datos = App\Salud\ProcedimientosCups::where( [
											['consulta_id', '=', $consulta->id]
										] )
									->get();
	//dd($datos);
?>

<br>

<div class="alert alert-success alert-dismissible fade in" style="display: none;" id="mensaje_alerta">
</div>

<div id="tabla_registros_procedimientos_cups_{{$consulta->id}}">
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Diagnóstico principal</th>
				<th>Diagnóstico relacionado</th>
				<th>Núm. autorización</th>
				<th>Código procedimiento</th>
				<th>Ambito de realización del procedimiento</th>
				<th>Finalidad del procedimiento</th>
				<th>Valor del procedimiento</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $records_list AS $record )
				<?php
					//dd( $record->get_fields_to_show() );
				?>
				<tr>
					<td> {{ $record->get_fields_to_show()->diagnostico_cie_principal_id->value }} </td>
					<td> {{ $record->get_fields_to_show()->codigo_cups->value }} </td>
					<td> {{ $record->get_fields_to_show()->valor_procedimiento->value }} </td>
					<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_procedimiento_cups' data-consulta_id="{{ $consulta->id }}" data-paciente_id="{{ $record->get_fields_to_show()->paciente_id->value }}" data-id="{{ $record->get_fields_to_show()->id }}"><i class='glyphicon glyphicon-trash'></i></button> </td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
            <tr>
                <td colspan="5">
                    <button style="background-color: transparent; color: #3394FF; border: none;" class="btn_nuevo_registro_procedimientos_cups">
                    	<i class="fa fa-btn fa-plus"></i> Agregar registro
                    	<span data-consulta_id="{{ $consulta->id }}"></span>
                    </button>
                </td>
            </tr>
        </tfoot>
	</table>

	@include('consultorio_medico.odontologia.procedimientos_cups.modal', [ 'consulta_id' => $consulta->id ])

</div>

@section('scripts10')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_nuevo_registro_procedimientos_cups").click(function(event){

				event.preventDefault();
		        
		        var consulta_id = $(this).children('span').attr('data-consulta_id');
				
		        $( '#modal_procedimientos_cups_' + consulta_id ).modal({backdrop: "static"});

		        $("#div_cargando").show();
		        
		        var modelo_id = 308;

		        var url = "{{ url('salud_procedimiento_cups/create') }}" + "?id_modelo=" + modelo_id + "&paciente_id=" + paciente_id + "&consulta_id=" + consulta_id;

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_procedimientos_cups_' + consulta_id).html(data);
				});
		    });

			$(document).on('click', '.btn_eliminar_registro_procedimientos_cups', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");

				if ( confirm('¿Desea eliminar el registro de procedimientos_cups para el diente # ' + fila.find('td').eq(0).html() ) )
				{
					$('#div_cargando').show();
	            	var url = "{{ url('salud_procedimiento_cups/delete') }}" + "/" + $(this).attr('data-id');

					$.get( url )
						.done(function( respuesta ) {
							$('#div_cargando').hide();
							fila.remove();
						});
				}
			});


			// GUARDAR 
			$(document).on("click",".btn_save_modal_procedimientos_cups",function(event){

		    	event.preventDefault();
		        
		        $(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );

		        var consulta_id = $(this).children('span').attr('data-consulta_id');
		        formulario = $('#modal_procedimientos_cups_' + consulta_id).find('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();

		        $.post(url, data, function (respuesta) {

		        	var fila = '<tr id="ultima_fila" style="display:none;"> <td> ' + respuesta.numero_diente.value + ' </td> <td> ' + respuesta.frio.value + ' </td> <td> ' + respuesta.caliente.value + ' </td> <td> ' + respuesta.percusion_horizontal.value + ' </td> <td> ' + respuesta.percusion_vertical.value + ' </td> <td> ' + respuesta.observaciones.value + ' </td> <td> <button type="button" class="btn btn-danger btn-xs btn_eliminar_registro_procedimientos_cups" data-consulta_id="' + respuesta.consulta_id.value + '" data-paciente_id="' + respuesta.paciente_id.value + '" data-id="' + respuesta.id + '"><i class="glyphicon glyphicon-trash"></i></button> </td> </tr>';

		        	$('#tabla_registros_procedimientos_cups_' + consulta_id).find('tbody:last').append( fila );

					$('#ultima_fila').fadeIn(1000);
					$('#ultima_fila').removeAttr('id');

		        	$('#modal_procedimientos_cups_' + consulta_id).modal('hide');
		            $('#contenido_modal_procedimientos_cups_' + consulta_id).html('');

			        $('#modal_procedimientos_cups_' + consulta_id).find('.btn_save_modal_procedimientos_cups').children('.fa-spinner').attr('class','fa fa-save');
			        $('#modal_procedimientos_cups_' + consulta_id).find('.btn_save_modal_procedimientos_cups').removeAttr( 'disabled' );
		        });
		    });

		});
	</script>
@endsection