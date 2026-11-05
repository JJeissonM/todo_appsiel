$(document).ready(function(){
	
	var direccion = location.href;
	var solicitud_calculo_fecha = null;

	$('#fecha_final_tnl').attr('readonly','readonly');
	$('#fecha_inicial_tnl').after('<div id="fecha_vacaciones_error" class="text-danger" style="margin-top:5px;"></div>');

	$('#nom_contrato_id').after( '<div id="fecha_ingreso" style="display:none;"> </div>' );
	$('#nom_contrato_id').after( '<div id="contrato_hasta" style="display:none;"> </div>' );
	$('#nom_contrato_id').after( '<div id="grupo_empleado_id" style="display:none;"> </div>' );

	// El formulario de creación puede abrir con un empleado ya seleccionado
	// (por restauración del navegador o por el combobox). También en ese caso
	// se necesitan cargar el grupo y las fechas del contrato.
	if ( $('#nom_contrato_id').val() != '' )
	{
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

				// Si el usuario alcanzó a ingresar la fecha y los días mientras se
				// cargaba el contrato, calcular tan pronto estén disponibles sus datos.
				intentar_calcular_fecha_final();
			});
	}

	$('#fecha_inicial_tnl').on( 'blur', function(){
		// Se valida al terminar la edición del campo. Los controles nativos de
		// fecha pueden disparar "change" al editar cada segmento (día, mes o
		// año), lo que interrumpía la escritura con alertas prematuras.
		if ( !es_fecha_completa_valida( $(this).val() ) )
		{
			mostrar_error_fecha('');
			if ( $(this).val() == '' )
			{
				$('#fecha_final_tnl').val('');
				$('#dias_no_habiles').val('');
				$('#cantidad_dias_tnl').val('');
				$('#bs_boton_guardar').hide();
			}
			return false;
		}

		if ( !validar_fecha_ingresada( $(this) ) )
		{
			return false;
		}

		calcular_fecha_final();
		
	});

	$('#cantidad_dias_tomados').on( 'keyup change', function(){


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

		if ( !validar_fecha_ingresada( $('#fecha_inicial_tnl') ) )
		{
			return false;
		}

		calcular_fecha_final();
		
	});

	$('#dias_compensados').on( 'change', function(){
		if ( $('#fecha_inicial_tnl').val() == '' || $('#cantidad_dias_tomados').val() == '' )
		{
			return false;
		}

		var dias_compensados = $(this).val() == '' ? 0 : Number($(this).val());
		var dias_tomados = Number($('#cantidad_dias_tomados').val());

		if ( !Number.isInteger(dias_compensados) || dias_compensados < 0 )
		{
			alert('Los días compensados deben ser un número entero mayor o igual a cero.');
			$(this).val(0);
			calcular_fecha_final();
			return false;
		}

		if ( dias_compensados > (dias_tomados / 2) )
		{
			alert('Los días compensados no pueden superar la mitad de los días de vacaciones reconocidos.');
			$(this).val(0);
			calcular_fecha_final();
			return false;
		}

		calcular_fecha_final();
	});

	function intentar_calcular_fecha_final()
	{
		if ( !es_fecha_completa_valida($('#fecha_inicial_tnl').val()) )
		{
			return false;
		}

		if ( $.trim($('#grupo_empleado_id').text()) == '' || $('#cantidad_dias_tomados').val() == '' )
		{
			return false;
		}

		if ( !validar_fecha_ingresada($('#fecha_inicial_tnl')) )
		{
			return false;
		}

		return calcular_fecha_final();
	}

	function calcular_fecha_final()
	{
		if ( !es_fecha_completa_valida( $('#fecha_inicial_tnl').val() ) )
		{
			return false;
		}

		if ( $.trim($('#grupo_empleado_id').text()) == '' || $('#cantidad_dias_tomados').val() == '' )
		{
			return false;
		}

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
			var url = '../get_fecha_final_vacaciones/' + $.trim($('#grupo_empleado_id').text()) + '/' + $('#fecha_inicial_tnl').val() + '/' + cantidad_dias_tomados + '/' + dias_compensados;
		}else{
			// Editando
			var url = '../../get_fecha_final_vacaciones/' + $.trim($('#grupo_empleado_id').text()) + '/' + $('#fecha_inicial_tnl').val() + '/' + cantidad_dias_tomados + '/' + dias_compensados;
		}

		if ( solicitud_calculo_fecha !== null )
		{
			solicitud_calculo_fecha.abort();
		}

		$('#div_cargando').show();
		solicitud_calculo_fecha = $.get( url )
			.done(function( data ) {
				solicitud_calculo_fecha = null;
	    		$('#div_cargando').hide();
                $('#fecha_final_tnl').val( data.fecha_fin );
                $('#dias_no_habiles').val( data.dias_no_habiles );
				if ( !validar_fecha_ingresada( $('#fecha_final_tnl') ) )
				{
					$('#dias_no_habiles').val('');
					$('#cantidad_dias_tnl').val('');
					$('#bs_boton_guardar').hide();
					return false;
				}
                actualizar_cantidad_dias_horas();
				validar_fecha_otras_novedades();
			})
			.fail(function( xhr ) {
				if ( xhr.statusText === 'abort' )
				{
					return false;
				}

				solicitud_calculo_fecha = null;
				$('#div_cargando').hide();
				$('#fecha_final_tnl').val('');
				$('#dias_no_habiles').val('');
				$('#cantidad_dias_tnl').val('');
				$('#bs_boton_guardar').hide();
				var mensaje = 'No fue posible calcular la programación de vacaciones.';
				if ( xhr.responseJSON && xhr.responseJSON.mensaje_error )
				{
					mensaje = xhr.responseJSON.mensaje_error;
				}
				alert(mensaje);
			});
	}

	function validar_fecha_ingresada( fecha_seleccionada )
	{
		var fecha = fecha_seleccionada.val();
		var fecha_inicial_tnl = $('#fecha_inicial_tnl').val();
		var fecha_ingreso = $('#fecha_ingreso').text();
		var contrato_hasta = $('#contrato_hasta').text();

		// No mostrar errores mientras se escribe manualmente una fecha. La
		// validación se realizará cuando el control entregue un valor completo.
		if ( !es_fecha_completa_valida( fecha ) )
		{
			return false;
		}

		// Si los datos del contrato todavía están cargando, no deben bloquear el
		// cálculo. Las restricciones se aplican únicamente cuando la fecha existe.
		if ( es_fecha_completa_valida(fecha_ingreso) && fecha_inicial_tnl < fecha_ingreso )
		{
			mostrar_error_fecha('La fecha ingresada (' + fecha + ') no puede ser menor a la fecha inicial del contrato: ' + fecha_ingreso + '.');
			$('#bs_boton_guardar').hide();
			return false;
		}

		// Los contratos indefinidos pueden no tener fecha final. En ese caso no
		// existe un límite superior para validar en el navegador.
		if ( es_fecha_completa_valida(contrato_hasta) && fecha > contrato_hasta )
		{
			mostrar_error_fecha('La fecha ingresada (' + fecha + ') no puede ser mayor a la fecha final del contrato: ' + contrato_hasta + '.');
			$('#bs_boton_guardar').hide();
			return false;
		}

		mostrar_error_fecha('');
		return true;
	}

	function mostrar_error_fecha( mensaje )
	{
		$('#fecha_vacaciones_error').text(mensaje);
	}

	function es_fecha_completa_valida( fecha )
	{
		if ( typeof fecha !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(fecha) )
		{
			return false;
		}

		var partes = fecha.split('-');
		var anio = Number(partes[0]);
		var mes = Number(partes[1]);
		var dia = Number(partes[2]);
		var fecha_objeto = new Date(anio, mes - 1, dia);

		return fecha_objeto.getFullYear() === anio &&
			fecha_objeto.getMonth() === mes - 1 &&
			fecha_objeto.getDate() === dia;
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
