<?php
	use App\Http\Controllers\Sistema\VistaController;

	$datos = App\Salud\DiagnosticoCie::where( [
											['consulta_id', '=', $consulta->id]
										] )
									->get();
	//dd($datos);
?>

<br>

<div class="alert alert-success alert-dismissible fade in" style="display: none;" id="mensaje_alerta">
</div>

<div id="contenido_seccion_modelo_{{$ID}}" class="contenido_seccion_modelo">
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Diagnóstico principal</th>
				<th>Código CIE</th>
				<th>Tipo de diagnóstico</th>
				<th>Observaciones</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $datos AS $linea )
				<tr>
					<td> {{ $linea->es_diagnostico_principal}} </td>
					<td> {{ $linea->codigo_cie}} </td>
					<td> {{ $linea->tipo_diagnostico_principal}} </td>
					<td> {{ $linea->observaciones}} </td>
					<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_diagnostico_cie'><i class='glyphicon glyphicon-trash'></i></button> </td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
            <tr>
                <td colspan="5">
                    <button style="background-color: transparent; color: #3394FF; border: none;" class="btn_nuevo_registro_diagnostico_cie"><i class="fa fa-btn fa-plus"></i> Agregar registro</button>
                </td>
            </tr>
        </tfoot>
	</table>
</div>

@section('scripts9')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_nuevo_registro_diagnostico_cie").click(function(event){

				event.preventDefault();
				console.log('hi');
				
		        $("#myModal2").modal({backdrop: "static"});

		        $("#myModal2").attr('style','font-size>: 0.8em;');

		        $("#div_cargando").show();

		        $("#myModal2 .modal-title").html('Ingreso registro de Diagnóstico');
		        
		        $(".btn_edit_modal").hide();

		        var url = "{{ url('salud_diagnostico_cie/create?id_modelo=309') }}";

				$.get( url, function( data ) {
			        $('#div_cargando').hide();

		            $('#contenido_modal2').html(data);
				});		        
		    });

			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
				fila.remove();
				$('#btn_nuevo').show();
				calcular_totales();
			});


			// GUARDAR 
			$('#btn_guardar').click(function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;	
				}
				
				var valor_total = parseFloat( $('#valor_total').val() );

				var total_valor = parseFloat( $('#total_valor').text().substring(1) );

				if ( valor_total != total_valor) {
					alert('El VALOR TOTAL PAGO no coincide con el valor total de los registros ingresados.');
					return false;
				}

				// Se obtienen todos los datos del formulario y se envían

						// Desactivar el click del botón
						$( this ).off( event );

						// Eliminar fila(s) de ingreso de registro vacia
						$('.linea_ingreso_default').remove();						

						// Se asigna la tabla de ingreso de registros a un campo hidden
						var tabla_registros_documento = $('#ingreso_registros').tableToJSON();
						$('#tabla_registros_documento').val( JSON.stringify(tabla_registros_documento) );

						// Enviar formulario
						habilitar_campos_form_create();
						$('#form_create').submit();	
					
			});

		});
	</script>
@endsection