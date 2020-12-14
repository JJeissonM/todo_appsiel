$(document).ready(function(){
	
	if ( $('#tipo_novedad_tnl').val() != 'incapacidad' )
	{
		ocultar_campos_incapacidad();
	}

	if ( $('#es_prorroga').val() == 0 )
	{
		$('#novedad_tnl_anterior_id').parent().parent().parent().parent().hide();
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
		$('#cantidad_dias_amortizados').parent().parent().parent().parent().hide();
		$('#cantidad_dias_pendientes_amortizar').parent().parent().parent().parent().hide();
		$('#es_prorroga').parent().parent().parent().parent().hide();
		$('#novedad_tnl_anterior_id').parent().parent().parent().parent().hide();
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
		$('#cantidad_dias_amortizados').parent().parent().parent().parent().show();
		$('#cantidad_dias_pendientes_amortizar').parent().parent().parent().parent().show();
		$('#es_prorroga').parent().parent().parent().parent().show();
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


	
	$('#es_prorroga').on( 'change', function(){
		
		if ( $(this).val() == 1 )
		{
			$('#novedad_tnl_anterior_id').parent().parent().parent().parent().show();
			$('#novedad_tnl_anterior_id').attr('required','required');
			/*
				llenar select novedad_tnl_anterior_id con incapacidades del empleado
			*/
		}else{
			$('#novedad_tnl_anterior_id').parent().parent().parent().parent().hide();
			$('#novedad_tnl_anterior_id').removeAttr('required');
		}

	});

	$('#novedad_tnl_anterior_id').on( 'change', function(){
		/*
			Agregar los días amortizados de la incapacidad anterior a esta que se está creando
		*/
	});

	function actualizar_cantidad_dias_horas()
	{

		var date_1 = new Date( $('#fecha_inicial_tnl').val() );
		var date_2 = new Date( $('#fecha_final_tnl').val() );

		/*
		 VALIDAR QUE NO HAYAN NOVEDADES ENTRE ESTAS FECHAS (FECHAS NO OCUPADAS)
		 */

		var day_as_milliseconds = 86400000;
		var diff_in_millisenconds = date_2 - date_1;
		var diff_in_days = diff_in_millisenconds / day_as_milliseconds + 1;

		$('#cantidad_dias_tnl').val( diff_in_days );
		$('#cantidad_horas_tnl').val( diff_in_days * 8 );

		$('#cantidad_dias_pendientes_amortizar').val( diff_in_days );

		if ( isNaN(diff_in_days) || diff_in_days < 0 )
		{ 
			$('#bs_boton_guardar').hide();
		}else
		{
			$('#bs_boton_guardar').show();
		}
	}

});