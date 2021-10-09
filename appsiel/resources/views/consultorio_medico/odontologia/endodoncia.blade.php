<?php
	use App\Http\Controllers\Sistema\VistaController;

	$datos = App\Salud\Endodoncia::where( [
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
			@foreach( $datos AS $linea )
				<tr>
					<td> {{ $linea->numero_diente}} </td>
					<td> {{ $linea->frio}} </td>
					<td> {{ $linea->caliente}} </td>
					<td> {{ $linea->percusion_horizontal}} </td>
					<td> {{ $linea->percusion_vertical}} </td>
					<td> {{ $linea->observaciones}} </td>
					<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_endodoncia'><i class='glyphicon glyphicon-trash'></i></button> </td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
            <tr>
                <td colspan="5">
                    <button style="background-color: transparent; color: #3394FF; border: none;" class="btn_nuevo_registro_endodoncia"><i class="fa fa-btn fa-plus"></i> Agregar registro</button>
                </td>
            </tr>
        </tfoot>
	</table>
</div>

@section('scripts8')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_nuevo_registro_endodoncia").click(function(event){

				event.preventDefault();
				
		        $("#myModal2").modal({backdrop: "static"});

		        $("#myModal2").attr('style','font-size>: 0.8em;');

		        $("#div_cargando").show();

		        $("#myModal2 .modal-title").html('Ingreso registro de endodoncia');
		        
		        $(".btn_edit_modal").hide();

		        var url = "{{ url('salud_endodoncia/create?id_modelo=308') }}";

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
			$(document).on("click",".btn_save_modal",function(event){

		    	event.preventDefault();

		        /*panel_id = $(this).parent('div').parent('div').parent('div').parent('div').attr('id');
				
				$( "#" + panel_id + " .div_spin").show();
		        
				$('#contenido_seccion_modelo_' + panel_id ).html( '' );*/
		        
		        formulario = $(this).prev('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();
				
				console.log([formulario,url]);

		        $.post(url, data, function (datos) {
		        	$( "#" + panel_id).find('.div_spin').hide();
					$('#contenido_seccion_modelo_' + panel_id ).html( datos );
		        });
		    });

		});
	</script>
@endsection