$(document).ready(function(){
	
	$('#valor_acumulado').attr('disabled','disabled');
	$('#valor_cuota').css("background-color","#ebebe4");

	$('#valor_cuota').focus( function(){
		$('#detalle').focus();
	});

	$('#valor_prestamo').keyup( function(){

		var valor_old = $(this).val();
		if ( $(this).val() <  $('#valor_acumulado').val() ) 
		{
			alert('El valor del prestamo es menor al valor acumulado. No se puede modificar.')
			$(this).val( "" );
		}else{
			if ( $('#numero_cuotas').val() != "" &&  $('#numero_cuotas').val() != 0 ) 
			{
				$('#valor_cuota').val( $(this).val() / $('#numero_cuotas').val() );
			}else{
				$('#valor_cuota').val( 0 );
			}
		}
		
	});

	$('#numero_cuotas').keyup( function(){
		if ( $('#numero_cuotas').val() != "" &&  $('#numero_cuotas').val() != 0 ) 
		{
			$('#valor_cuota').val( $('#valor_prestamo').val() / $('#numero_cuotas').val() );
		}else{
			$('#valor_cuota').val( 0 );
		}
		
	});

});