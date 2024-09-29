<?php
	
	$user = \Auth::user();

	if ( $user->hasRole('SuperAdmin') || $user->hasRole('Administrador') || $user->hasRole('Jefe de almacén') ) 
    {
    	$pdvs = App\VentasPos\Pdv::where([['estado','<>', 'Inactivo']])->get();
    }else{
    	$pdvs = App\VentasPos\Pdv::where( [['cajero_default_id','=', $user->id],['estado','<>', 'Inactivo']] )->get();
    }
?>


@extends('layouts.principal')

@section('estilos_2')
	<style>
		.tienda{

		}

		.tienda div.caja{
			border: 2px solid gray;
		    margin: -40px 10% 0px;
		    height: 220px;
		}

		.datos_pdv{
			padding: 10px;
		}

		table tr td {
			padding: 5px;
		}

	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<input type="hidden" id="ids_facturas" name="ids_faturas">
			
			<?php
				$cant_cols = 3;
				$i = $cant_cols;
		    ?>

			@foreach( $pdvs as $pdv )

				@if($i % $cant_cols == 0)
		            <div class="row">
		        @endif

					@include('ventas_pos.index_una_casita')

				<?php
					$i++;
				?>

				@if($i % $cant_cols == 0)
				</div>
				<br/><br/>
				@endif

			@endforeach

		</div>
	</div>

	@include('components.design.ventana_modal',['titulo'=>'','texto_mensaje'=>''])

	@include( 'components.design.ventana_modal2',[ 'titulo2' => '', 'texto_mensaje2' => '', 'clase_tamanio' => 'modal-lg' ] )

	<input type="hidden" name="convertir_facturas_pos_a_electronicas_en_acumulacion" id="convertir_facturas_pos_a_electronicas_en_acumulacion" value="{{ (int)config('ventas_pos.convertir_facturas_pos_a_electronicas_en_acumulacion') }}">

@endsection

@section('scripts')
	
<script type="text/javascript">
		
	var pdv_id;
	var continuar = true;
	var arr_ids_facturas;
	var restantes;

	$(document).ready(function(){

		var btn_acumular;

		$(".btn_acumular").click(function(event){

			// Desactivar el click del botón
			$( this ).attr( 'disabled', 'disabled');
			$( this ).off( event );

			$("#myModal").modal({backdrop: "static"});
			$("#div_spin").show();
			$("#myModal .close").hide();
			$(".btn_close_modal").hide();
			$(".btn_edit_modal").hide();
			$(".btn_save_modal").hide();

			btn_acumular = $(this);

			$("#ids_facturas").val($(this).attr('data-ids_facturas'));
			
			$('#contenido_modal').html( '<h1 style="text-align:center;"> <small>Por favor espere</small> <br> Validando Existencias... </h1>' );

			validar_existencias();
		});	

		function validar_existencias()
		{
			pdv_id = btn_acumular.attr('data-pdv_id');

			$.ajax({
				type: "GET",
				url: "{{url('pos_factura_validar_existencias')}}" + "/" + pdv_id,
				async: true,
				success : function(data) {
					if ( data != 1 ) // Cuando falla la validacion. data = vista_html
					{
						$(".btn_close_modal").show();
						$("#ids_facturas").val('[]');
						$('#contenido_modal').html( '<h1 style="text-align:center;"> <small>Por favor espere</small>  <br> Validación de existencias: <i class="fa fa-remove"></i> </h1>' + data );
					}else{
						
						arr_ids_facturas = JSON.parse($("#ids_facturas").val());

						restantes = arr_ids_facturas.length;

						$('#contenido_modal').html( '<h1 style="text-align:center;"> <small>Por favor espere</small>  <br> Validación de existencias completada exitosamente: <i class="fa fa-check"></i> <br> Acumulando facturas POS... <span id="contador_facturas" style="color:#9c27b0">' + restantes + '</span> facturas restantes.</h1>' );

						// fires off the first call 
						getShelfRecursive();
					}
				},
				error : function( data, textStatus, xhr ) {
					$("#ids_facturas").val('[]');
					$(".btn_close_modal").show();
					$('#contenido_modal').html( '<h1 style="text-align:center;">  <small style="color:red;"> <i class="fa fa-times-circle"></i> Error en Validacion de existencias. </small> <br> Code: ' + data.status + '  <br> Status: ' + textStatus + " - " + xhr + ' </h1>' );
				}
			});

			return continuar;
		}
		
		// the recursive function 
		function getShelfRecursive() { 
			
			// terminate if array exhausted 
			if (arr_ids_facturas.length === 0) 
			{
				$("#div_spin").hide();
				location.reload();

				return; 
			}

			// pop top value 
			var factura_id = arr_ids_facturas[0]; 
			arr_ids_facturas.shift(); 
			
			// ajax request 
			$.get("{{url('pos_acumular_una_factura')}}" + "/" + factura_id, function(){

				if ( $('#convertir_facturas_pos_a_electronicas_en_acumulacion').val() == 0) {
					// call completed - so start next request 
					restantes--;
					document.getElementById('contador_facturas').innerHTML = restantes;
					getShelfRecursive();	
				}else{
					$.get("{{url('pos_acumulacion_convertir_en_factura_electronica')}}" + "/" + factura_id, function(){
						restantes--;
						document.getElementById('contador_facturas').innerHTML = restantes;
						getShelfRecursive();
					});
				}

				
			}); 
		}

		$(document).on('click',".btn_consultar_facturas",function(event){
			event.preventDefault();

			$('#contenido_modal2').html('');
			$('#div_spin2').fadeIn();

			$("#myModal2").modal(
				{backdrop: "static"}
			);

			$("#myModal2 .modal-title").text('Consulta de ' + $(this).attr('data-lbl_ventana'));

			$("#myModal2 .btn_edit_modal").hide();
			$("#myModal2 .btn_save_modal").hide();
			
			var url = "{{ url('pos_consultar_documentos_pendientes') }}" + "/" + $(this).attr('data-pdv_id') + "/" + $(this).attr('data-fecha_primera_factura') + "/" + $(this).attr('data-fecha_hoy') + "?view=" + $(this).attr('data-view') + "&id=20";

			$.get( url, function( respuesta ){
				$('#div_spin2').hide();
				$('#contenido_modal2').html( respuesta );
			});
		});

	});
</script>
@endsection