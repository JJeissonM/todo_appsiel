$(document).ready(function(){
	
	if ( $('#tipo_novedad_tnl').val() != 'incapacidad' )
	{
		ocultar_campos_incapacidad();
	}

	function ocultar_campos_incapacidad()
	{
		$('#lbl_detalles_de_la_incapacidad').parent().parent().parent().hide();
		$('#codigo_diagnostico_incapacidad').parent().parent().parent().parent().hide();
		$('#numero_incapacidad').parent().parent().parent().parent().hide();
		$('#fecha_expedicion_incapacidad').parent().parent().parent().parent().hide();
		$('#origen_incapacidad').parent().parent().parent().parent().hide();
		$('#clase_incapacidad').parent().parent().parent().parent().hide();
		$('#fecha_incapacidad').parent().parent().parent().parent().hide();
		$('#valor_a_pagar_eps').parent().parent().parent().parent().hide();
		$('#valor_a_pagar_arl').parent().parent().parent().parent().hide();
		$('#valor_a_pagar_empresa').parent().parent().parent().parent().hide();
	}

	function mostrar_campos_incapacidad()
	{
		$('#lbl_detalles_de_la_incapacidad').parent().parent().parent().show();
		$('#codigo_diagnostico_incapacidad').parent().parent().parent().parent().show();
		$('#numero_incapacidad').parent().parent().parent().parent().show();
		$('#fecha_expedicion_incapacidad').parent().parent().parent().parent().show();
		$('#origen_incapacidad').parent().parent().parent().parent().show();
		$('#clase_incapacidad').parent().parent().parent().parent().show();
		$('#fecha_incapacidad').parent().parent().parent().parent().show();
		$('#valor_a_pagar_eps').parent().parent().parent().parent().show();
		$('#valor_a_pagar_arl').parent().parent().parent().parent().show();
		$('#valor_a_pagar_empresa').parent().parent().parent().parent().show();
	}
	
	$('#tipo_novedad_tnl').on( 'change', function(){
		
		if ( $(this).val() == 'incapacidad' )
		{
			mostrar_campos_incapacidad();
		}else{
			ocultar_campos_incapacidad();
		}

	});

	$('#fecha_inicial_tnl').on( 'change', function(){

		actualizar_cantidad_dias_horas()
		
	});

	$('#fecha_final_tnl').on( 'change', function(){

		actualizar_cantidad_dias_horas()
		
	});

	function actualizar_cantidad_dias_horas()
	{
		
		var date_1 = new Date( $('#fecha_inicial_tnl').val() );
		var date_2 = new Date( $('#fecha_final_tnl').val() );

		var day_as_milliseconds = 86400000;
		var diff_in_millisenconds = date_2 - date_1;
		var diff_in_days = diff_in_millisenconds / day_as_milliseconds + 1;



		console.log( diff_in_days );
		$('#cantidad_dias_tnl').val( diff_in_days );
		$('#cantidad_horas_tnl').val( diff_in_days * 8 );

		if ( isNaN(diff_in_days) || diff_in_days < 0 )
		{ 
			$('#bs_boton_guardar').hide();
		}else
		{
			$('#bs_boton_guardar').show();
		}
	}


	$('#numero_cuotas').keyup( function(){
		if ( $('#numero_cuotas').val() != "" &&  $('#numero_cuotas').val() != 0 ) 
		{
			$('#valor_cuota').val( $('#valor_prestamo').val() / $('#numero_cuotas').val() );
		}else{
			$('#valor_cuota').val( 0 );
		}
		
	});

});