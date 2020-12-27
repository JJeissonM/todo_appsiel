@extends('layouts.create')

@section('campos_adicionales')
	<br>
	<div class="container-fluid">
		
		{{ Form::Spin(64) }}

		<div id="div_resultados">
			hello los está llamando
		</div>

	</div>
@endsection

@section('scripts2')
	<script type="text/javascript">
		
		$(document).ready(function(){
			
			$('#fecha').val( get_fecha_hoy() );

			$('#plantilla_plan_clases_id').focus();

			var sum;
			
			// PARA BILLETES
			$('#plantilla_plan_clases_id').on('change',function(){
				
				if ( $(this).val() == '' )
				{ 

					return false; }
				
				$('#div_cargando').show();
            	var url = '../tesoreria/get_tabla_movimiento';

				$.get( url, { movimiento: 'entrada', fecha_desde: $('#fecha').val(), fecha_hasta: $('#fecha').val(), teso_caja_id: $('#teso_caja_id').val() } )
					.done(function( respuesta ) {
						$('#div_cargando').hide();
						$('#div_mov_entrada').html( respuesta[0] );
						$('#total_mov_entradas').val( respuesta[1] );
						calcular_total_saldo();
					});
			});



		});

	</script>
@endsection