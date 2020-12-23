$(document).ready(function(){
	
	var direccion = location.href;

	$('#nom_contrato_id').after( '<div id="fecha_ingreso" style="display:none;"> </div>' );
	$('#nom_contrato_id').after( '<div id="contrato_hasta" style="display:none;"> </div>' );

	if( direccion.search("edit") != -1)
	{
		// Editando
		get_datos_contrato();
	}

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
		//$('#cantidad_dias_amortizados').parent().parent().parent().parent().hide();
		//$('#cantidad_dias_pendientes_amortizar').parent().parent().parent().parent().hide();
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
		//$('#cantidad_dias_amortizados').parent().parent().parent().parent().show();
		//$('#cantidad_dias_pendientes_amortizar').parent().parent().parent().parent().show();
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

	$('#nom_contrato_id').on( 'change', function(){

		$('#fecha_inicial_tnl').val('');
		$('#fecha_final_tnl').val('');

		if ( $(this).val() == '' )
		{ 
			$('#fecha_ingreso').text('');
			$('#contrato_hasta').text('');
			return false;
		}

		get_datos_contrato();
		
	});

	function get_datos_contrato()
	{
		$('#div_cargando').show();
		var url = '../../get_datos_contrato/' + $('#nom_contrato_id').val();

		$.get( url )
			.done(function( data ) {
	    		$('#div_cargando').hide();
                $('#fecha_ingreso').text( data.fecha_ingreso );
                $('#contrato_hasta').text( data.contrato_hasta );
			});
	}

	$('#fecha_inicial_tnl').on( 'change', function(){
		
		validar_fecha_ingresada( $(this) );

		validar_fecha_otras_novedades( $(this) );

		actualizar_cantidad_dias_horas()
		
	});

	$('#fecha_final_tnl').on( 'change', function(){

		validar_fecha_ingresada( $(this) );

		validar_fecha_otras_novedades( $(this) );

		actualizar_cantidad_dias_horas()
		
	});

	function validar_fecha_ingresada( fecha_seleccionada )
	{
		if ( $('#fecha_ingreso').text() == '' )
		{
			fecha_seleccionada.val('');
			alert('Debe selecionar a un empleado.');
			return false;
		}

		if ( fecha_seleccionada.val() < $('#fecha_ingreso').text()  )
		{
			fecha_seleccionada.val('');
			alert('La fecha ingresada no puede ser MENOR a la fecha inicial del contrato del empleado: ' + $('#fecha_ingreso').text() );
			return false;
		}

		if ( fecha_seleccionada.val() > $('#contrato_hasta').text() )
		{
			fecha_seleccionada.val('');
			alert('La fecha ingresada no puede ser MAYOR a la fecha final del contrato del empleado: ' + $('#contrato_hasta').text() );
			return false;
		}
	}

	function validar_fecha_otras_novedades( obj )
	{

		$('#div_cargando').show();
		var fecha_inicial_tnl = $('#fecha_inicial_tnl').val();
		var fecha_final_tnl = $('#fecha_final_tnl').val();

		if ( fecha_inicial_tnl == '') { fecha_inicial_tnl = 0; }
		if ( fecha_final_tnl == '') { fecha_final_tnl = 0; }

		if( direccion.search("edit") == -1)
		{
			// Creando
			var url = '../validar_fecha_otras_novedades/' + fecha_inicial_tnl + '/' + fecha_final_tnl + '/' + $('#nom_contrato_id').val() + '/0';
		}else{
			// Editando
			var url = '../../validar_fecha_otras_novedades/' + $('#fecha_inicial_tnl').val() + '/' + $('#fecha_final_tnl').val() + '/' + $('#nom_contrato_id').val() + '/' + JSON.parse( $('#datos_registro').val() ).id;
		}

		$.get( url )
			.done(function( data ) {
	    		$('#div_cargando').hide();
                if (data == 1)
                {
                	alert('Ya existen novedades de TNL ingresadas para ese empleado en las fechas seleccionadas.');
                	$('#fecha_inicial_tnl').val('');
                	$('#fecha_final_tnl').val('');
                }
			});
	}

	
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