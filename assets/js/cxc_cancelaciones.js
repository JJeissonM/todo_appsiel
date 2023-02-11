$(document).ready( function(){
	
	var sumas_iguales = 0;
	var LineaNum = 0;
	var debito, credito;

	$('#core_tipo_doc_app_id').change(function(){
		$('#fecha').focus();
	});

	$('#valor_total').keyup(function(event){
		
		validar_valor_numerico( $(this) );
		
		var x = event.which || event.keyCode;
		if( x==13 ){
			$(this).next().focus();		
		}
	});

	function validar_valor_numerico(celda)
	{
		var valor = celda.val();
		if ( $.isNumeric(valor)  && valor > 0 ) {
			celda.attr('style','background-color:white;');
			ok = true;
		}else{
			celda.attr('style','background-color:#FF8C8C;');
			celda.focus();
			ok = false;
		}
		return ok;
	}

	$('#btn_continuar1').click(function(event){
		event.preventDefault();

		var ph_propiedad_id = $('#ph_propiedad_id').val().split('a3p0');
		$('#core_tercero_id').val( ph_propiedad_id[0] );
		$('#codigo_referencia_tercero').val( ph_propiedad_id[1] );
		

		if ( validar_requeridos() ) {

			if ( $('#tipo_movimiento').val() != "Anticipo" ) {
				
				$('#div_cargando').show();
						
				var url = '../cxc/get_cartera_inmueble/' + ph_propiedad_id[1];
				$.get( url, function( datos ) {
			        $('#div_cargando').hide();
			        //console.log(datos);
			        var tablas = datos.split('a3p0')

			        $('#btn_continuar1').hide();
			        $('#btn_cancelar1').show();

					$('#div_documentos_cartera').show();
					$('#div_documentos_a_cancelar').show();

					$('#div_cartera').html( tablas[0] );

					$("#div_cartera input:text").first().focus();
				});
			}else{
				$('#btn_continuar1').hide();
		        $('#btn_cancelar1').show();
		        $('#btn_guardar2').show();
				$('#btn_guardar2').focus();
			}

			deshabilitar_campos_form_create();
		}
	});


	$(document).on('click', '.btn_agregar_documento', function(event) {
		event.preventDefault();
		var fila = $(this).closest("tr");

		var celda = fila.find("input:text");

		if( validar_valor_aplicar( celda ) ){
			//var celda_borrar = "<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_documento'><i class='fa fa-btn fa-trash'></i></button> </td>";
			var celda_borrar = "<td> &nbsp; </td>";

			var valor = celda.val();
			fila.find("td:last").text( valor );
			fila.find("td:last").attr('class', 'valor' );

			//console.log( fila );

			var id_encabezado_documento = fila.attr('id');

			fila.prepend( "<td style='color: white;'> " + id_encabezado_documento + " </td>" );

			fila.append( celda_borrar );

			$('#div_documentos_a_cancelar').show();
			$('#documentos_a_cancelar').find('tbody:last').append( fila );

			$("#div_cartera input:text").first().focus();	
			calcular_totales();

			var valor_total = parseFloat( $('#valor_total').val() );

			var total_valor = parseFloat( $('#total_valor').text().substring(1) );

			if ( valor_total == total_valor) {
				$('#btn_guardar').show();
				$('#btn_guardar').focus();
			}else{
				$('#btn_guardar').hide();
			}
		}
	});

	/*
	** Al eliminar una fila
	*/
	// Se utiliza otra forma con $(document) porque el $('#btn_eliminar') no funciona pues
	// es un elemento agregado por ajax (despues de que se cargó la página)
	$(document).on('click', '.btn_eliminar', function(event) {
		event.preventDefault();
		var fila = $(this).closest("tr");
		fila.remove();
		$('#btn_nuevo').show();
		calcular_totales();
	});


	$('#btn_cancelar1').click(function(event){
		event.preventDefault();
		$('#div_cartera').html('');

		//Se resetean las filas del listado de pendiente por aplicar
		$('#documentos_a_cancelar').find('tbody').html( '' );
		
        $('#btn_cancelar1').hide();
        $('#div_documentos_cartera').hide();
        $('#div_documentos_a_cancelar').hide();
        $('#btn_continuar1').show();

        habilitar_campos_form_create();
	});


	// GUARDAR 
	$('#btn_guardar').click(function(event){
		event.preventDefault();

		var ph_propiedad_id = $('#ph_propiedad_id').val().split('a3p0');
		$('#core_tercero_id').val( ph_propiedad_id[0] );
		$('#codigo_referencia_tercero').val( ph_propiedad_id[1] );
		
		if ( $('#total_valor').text().substring(1) == 0) {
			alert('No ha ingresado registros.');
			$('#btn_nuevo').focus();
			return false;
		}

		// Se obtienen todos los datos del formulario y se envían
		// Se validan nuevamente los campos requeridos
		var control = 1;
		$( "*[required]" ).each(function() {
			if ( $(this).val() == "" ) {
			  $(this).focus();
			  control = 0;
			  alert('Este campo es requerido.');
			  return false;
			}else{
			  control = 1;
			}
		});

		if (control==1) {	

			// Desactivar el click del botón
			$( this ).off( event );
							
			// Eliminar fila de ingreso de registro vacia
			var object = $('#combobox_servicios').val();	
			if( typeof object == typeof undefined){
				// Si no hay linea de ingreso de registros
				// Todo bien
				//alert('Todo bien.');
			}else{
				var fila = $('#combobox_servicios').closest("tr");
				fila.remove();
			}

			// Se asigna la tabla de ingreso de registros a un campo hidden
			var documentos_a_cancelar = $('#documentos_a_cancelar').tableToJSON();
			$('#tabla_documentos_a_cancelar').val(JSON.stringify(documentos_a_cancelar));

			habilitar_campos_form_create();

			// Enviar formulario
			habilitar_campos_form_create();
			$('#form_create').submit();			
		}else{
			alert('Faltan campos por llenar.');
		}
			
	});

	// GUARDAR ANTICIPO
	$('#btn_guardar2').click(function(event){
		event.preventDefault();

		var ph_propiedad_id = $('#ph_propiedad_id').val().split('a3p0');
		$('#core_tercero_id').val( ph_propiedad_id[0] );
		$('#codigo_referencia_tercero').val( ph_propiedad_id[1] );
		
		// Se obtienen todos los datos del formulario y se envían
		// Se validan nuevamente los campos requeridos
		var control = 1;
		$( "*[required]" ).each(function() {
			if ( $(this).val() == "" ) {
			  $(this).focus();
			  control = 0;
			  alert('Este campo es requerido.');
			  return false;
			}else{
			  control = 1;
			}
		});

		if (control==1) {
			$('#tabla_documentos_a_cancelar').val( 'No' );
			habilitar_campos_form_create();

			// Enviar formulario
			habilitar_campos_form_create();
			$('#form_create').submit();			
		}else{
			alert('Faltan campos por llenar.');
		}
			
	});

	function calcular_totales(){
		var sum = 0.0;
		sum = 0.0;
		$('.valor').each(function()
		{
		    var cadena = $(this).text();
		    sum += parseFloat( cadena );
		});

		$('#total_valor').text("$"+sum.toFixed(2));

		if ( $('#valor_total').val() != '' ) {
			var valor_total = parseFloat( $('#valor_total').val() );
		}else{
			var valor_total = 0;
		}

		var diferencia = valor_total - sum;

		$('#lbl_total_pendiente').text( "Pendiente: " );
		$('#total_pendiente').text( "$" + diferencia.toFixed(2) );
	}

	function validar_requeridos(){
		$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = false;
					  alert('Este campo es requerido.');
					  return false;
					}else{
					  control = true;
					}
				});
		return control;
	}

	function validar_valor_aplicar(celda){
		var fila = celda.closest("tr");
		var ok;

		var valor = celda.val();
		var valor_total = $('#valor_total').val();

		if( $.isNumeric( valor ) ){
			valor = parseFloat( valor );
		}

		if( $.isNumeric( valor_total ) ){
			valor_total = parseFloat( valor_total );
		}

		var saldo_pendiente = fila.find('td.col_saldo_pendiente').text();
		saldo_pendiente = parseFloat( saldo_pendiente.replace( /\./g, "" ) );

		if( valor > 0  && valor <= saldo_pendiente  && valor <= valor_total) {
			celda.attr('style','background-color:white;');
			ok = true;
		}else{
			celda.attr('style','background-color:#FF8C8C;');
			celda.focus();
			ok = false;
		}

		return ok;
	}

	

	function deshabilitar_campos_form_create()
	{
		$('#fecha').attr('disabled','disabled');
		$('#tipo_movimiento').attr('disabled','disabled');
		$('#valor_total').attr('disabled','disabled');

		// se oculta la caja de texto del terceor y se muestra el select real
		$('.custom-combobox').hide();
		$('#core_tercero_id_no').show();
		$('#core_tercero_id_no').attr('disabled','disabled');

		$('#ph_propiedad_id').show();
		$('#ph_propiedad_id').attr('disabled','disabled');

	}

	function habilitar_campos_form_create()
	{
		$('#fecha').removeAttr('disabled');
		$('#tipo_movimiento').removeAttr('disabled');
		$('#valor_total').removeAttr('disabled');

		$('.custom-combobox').show();
		$('#core_tercero_id_no').hide();
		$('#ph_propiedad_id').hide();

		$('#core_tercero_id_no').removeAttr('disabled');
		
		$('#ph_propiedad_id').removeAttr('disabled');

	}

} );