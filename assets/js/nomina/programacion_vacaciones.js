$(document).ready(function(){
	
	var direccion = location.href;

	$('#fecha_final_tnl').attr('readonly','readonly');

	$('#nom_contrato_id').after( '<div id="fecha_ingreso" style="display:none;"> </div>' );
	$('#nom_contrato_id').after( '<div id="contrato_hasta" style="display:none;"> </div>' );
	$('#nom_contrato_id').after( '<div id="grupo_empleado_id" style="display:none;"> </div>' );

	if( direccion.search("edit") != -1)
	{
		// Editando
		get_datos_contrato();
	}

	$('#nom_contrato_id').on( 'change', function(){

		$('#fecha_inicial_tnl').val('');
		$('#fecha_final_tnl').val('');

		if ( $(this).val() == '' )
		{ 
			$('#fecha_ingreso').text('');
			$('#contrato_hasta').text('');
			$('#grupo_empleado_id').text('');
			return false;
		}

		get_datos_contrato();
		
	});

	function get_datos_contrato()
	{
		$('#div_cargando').show();

		var url = '../get_datos_contrato/' + $('#nom_contrato_id').val();

		if( direccion.search("edit") != -1)
		{
			// Editando
			var url = '../../get_datos_contrato/' + $('#nom_contrato_id').val();
		}		

		$.get( url )
			.done(function( data ) {
	    		$('#div_cargando').hide();
                $('#fecha_ingreso').text( data.fecha_ingreso );
                $('#contrato_hasta').text( data.contrato_hasta );
                $('#grupo_empleado_id').text( data.grupo_empleado_id );
			});
	}

	$('#fecha_inicial_tnl').on( 'change', function(){
		
		console.log( $(this).val() );

		validar_fecha_ingresada( $(this) );

		calcular_fecha_final();

		validar_fecha_otras_novedades();
		
	});

	$('#cantidad_dias_tomados').on( 'keyup', function(){


		if( $('#fecha_inicial_tnl').val() == '' )
		{
			return false;
		}

		if ( !validar_input_numerico( $('#cantidad_dias_tomados') ) )
		{
			alert('Debe ingresar una cantidad de días tomados válida.');
			$('#cantidad_dias_tomados').val('');
			return false;
		}

		if ( $('#cantidad_dias_tomados').val() == 0 )
		{
			alert('Debe ingresar una cantidad de días tomados.');
			$('#cantidad_dias_tomados').val('');
			return false;
		}

		validar_fecha_ingresada( $('#fecha_inicial_tnl') );

		calcular_fecha_final();

		validar_fecha_otras_novedades();
		
	});

	function calcular_fecha_final()
	{
		var dias_compensados = 0;
		if( $('#dias_compensados').val() != '' )
		{
			dias_compensados = $('#dias_compensados').val();
		}

		var cantidad_dias_tomados = 0;
		if( $('#cantidad_dias_tomados').val() != '' )
		{
			cantidad_dias_tomados = $('#cantidad_dias_tomados').val();
		}

		if( direccion.search("edit") == -1)
		{
			// Creando
			var url = '../get_fecha_final_vacaciones/' + $('#grupo_empleado_id').text() + '/' + $('#fecha_inicial_tnl').val() + '/' + cantidad_dias_tomados + '/' + dias_compensados;
		}else{
			// Editando
			var url = '../../get_fecha_final_vacaciones/' + $('#grupo_empleado_id').text() + '/' + $('#fecha_inicial_tnl').val() + '/' + cantidad_dias_tomados + '/' + dias_compensados;
		}

		$.get( url )
			.done(function( data ) {
	    		$('#div_cargando').hide();
                $('#fecha_final_tnl').val( data.fecha_fin );
                $('#dias_no_habiles').val( data.dias_no_habiles );
                validar_fecha_ingresada( $('#fecha_final_tnl') );
                actualizar_cantidad_dias_horas();
			});		
	}

	function validar_fecha_ingresada( fecha_seleccionada )
	{
		var fecha = fecha_seleccionada.val();
		var fecha_inicial_tnl = $('#fecha_inicial_tnl').val();

		if ( $('#fecha_ingreso').text() == '' )
		{
			fecha_seleccionada.val('');
			alert('Debe selecionar a un empleado.');
			return false;
		}

		if ( fecha_inicial_tnl < $('#fecha_ingreso').text()  )
		{
			fecha_seleccionada.val('');
			alert('La fecha ingresada (' + fecha + ') no puede ser MENOR a la fecha inicial del contrato del empleado: ' + $('#fecha_ingreso').text() );
			return false;
		}

		if ( fecha_seleccionada.val() > $('#contrato_hasta').text() )
		{
			fecha_seleccionada.val('');
			alert('La fecha ingresada (' + fecha + ') no puede ser MAYOR a la fecha final del contrato del empleado: ' + $('#contrato_hasta').text() );
			return false;
		}
	}

	function validar_fecha_otras_novedades()
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

	function actualizar_cantidad_dias_horas()
	{
		console.log( $('#fecha_inicial_tnl').val() );
		var date_1 = new Date( $('#fecha_inicial_tnl').val() );
		var date_2 = new Date( $('#fecha_final_tnl').val() );
		console.log([date_1,date_2,diff_in_millisenconds,diff_in_days]);

		var day_as_milliseconds = 86400000;
		var diff_in_millisenconds = date_2 - date_1;
		var diff_in_days = diff_in_millisenconds / day_as_milliseconds + 1;

		$('#cantidad_dias_tnl').val( diff_in_days );
		console.log([date_1,date_2,diff_in_millisenconds,diff_in_days]);
		//$('#cantidad_horas_tnl').val( diff_in_days * 8 );

		//$('#cantidad_dias_pendientes_amortizar').val( diff_in_days );

		if ( isNaN(diff_in_days) || diff_in_days < 0 )
		{ 
			$('#bs_boton_guardar').hide();
		}else
		{
			$('#bs_boton_guardar').show();
		}
	}

});