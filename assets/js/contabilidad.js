$(document).ready( function(){
	
	var sumas_iguales = 0;
	var LineaNum = 0;
	var debito, credito;

	$('#core_tipo_doc_app_id').change(function(){
		$('#fecha').focus();
	});

	$('#fecha').keyup(function(event){
		var x = event.which || event.keyCode;
		if(x==13){
			$('#core_tercero_id').focus();				
		}		
	});

	$('#btn_continuar1').click(function(event){
		event.preventDefault();
		if (validar_requeridos()) {
			$('#div_ingreso_registros').show();
			reset_form_registro();
	        $("#myModal").modal(
	        	{backdrop: "static",keyboard: 'true'}
	        );
		}
	});

	/*
	**	Agregar NUEVA línea de ingreso de datos
	*/
	$("#btn_nuevo").click(function(event){
		event.preventDefault();
		nueva_linea_ingreso_datos();
    });

    function nueva_linea_ingreso_datos(){
    	LineaNum ++;
    	$('#div_cargando').fadeIn();
		
		var url = '../contab_get_fila/' + LineaNum;
		$.get( url, function( datos ) {
	        $('#div_cargando').hide();

	        $('#ingreso_registros').find('tbody:first').append( datos );

	        $('#combobox_cuentas').focus();

	        $('#btn_nuevo').hide();
		});
    }

   	$(document).on('change', '#combobox_cuentas', function() {
		if ( $(this).val() != '' ) {
			$('#combobox_terceros').focus();
		}else{ 
			$(this).focus();
		}

	} );

	$(document).on('click', '.btn_confirmar', function(event) {
		event.preventDefault();
		var fila = $(this).closest("tr");
		var ok = validar_linea();
		if( ok ) {
			var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
	        var cuenta = '<span style="color:white;">' + $('#combobox_cuentas').val() + '-</span>' + $( "#combobox_cuentas option:selected" ).text();
	        var tercero = '<span style="color:white;">' + $('#combobox_terceros').val() + '-</span>' + $( "#combobox_terceros option:selected" ).text();
	        var detalle = $('#col_detalle').val();
	        var debito = $('#col_debito').val();
	        var credito = $('#col_credito').val();
	        
	        if(debito == ''){
	        	debito = 0; // Para no sumar una caja de texto vacía
	        }
	        
	        if( credito == ''){
	        	credito = 0;
	        }

	        $('#ingreso_registros').find('tbody:last').append('<tr id="fila_'+LineaNum+'" >' +
															'<td id="cuenta_'+LineaNum+'">' + cuenta + '</td>'+
															'<td id="tercero_'+LineaNum+'">' + tercero + '</td>'+
															'<td id="detalle_'+LineaNum+'">' + detalle + '</td>'+
															'<td id="debito_'+LineaNum+'"  class="debito">$' + debito + '</td>'+
															'<td id="credito_'+LineaNum+'"  class="credito">$' + credito + '</td>'+
															'<td>'+btn_borrar+'</td>'+
															'</tr>');
	       	
	       	calcular_totales();
	       	fila.remove();
	       	nueva_linea_ingreso_datos();
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


	// GUARDAR
	$('#btn_guardar').click(function(event){
		event.preventDefault();

		//$('#core_tercero_id').val( $('#ph_propiedad_id').val() );
		$('#codigo_referencia_tercero').val( 0 );
		
		$('#valor_total').val( $('#total_debito').text().substring(1) )

		// Se obtienen todos los datos del formulario y se envían
		// Se validan nuevamente los campos requeridos
		var control = 1;
		$( "*[required]" ).each(function() {
			if ( $(this).val() == "" ) {
			  $(this).focus();
			  control = 0;
			  alert('Este campo es requerido. '+ $(this).attr('name') 	);
			  return false;
			}else{
			  control = 1;
			}
		});

		if (control==1)
		{
			if ( $('#sumas_iguales').text( ) == 0 )
			{
				// Desactivar el click del botón
				$( this ).off( event );

				// Eliminar fila de ingreso de registro vacia
				var object = $('#combobox_cuentas').val();	
				if( typeof object == typeof undefined){
					// Si no hay linea de ingreso de registros
					// Todo bien
					//alert('Todo bien.');
				}else{
					var fila = $('#combobox_cuentas').closest("tr");
					fila.remove();
				}

				// Se asigna la tabla de ingreso de registros a un campo hidden
				var tabla_registros_documento = $('#ingreso_registros').tableToJSON();
				$('#tabla_registros_documento').val( JSON.stringify(tabla_registros_documento) );

				// Enviar formulario
				$('#form_create').submit();
			}else{
				alert('El asiento contable está descuadrado.')
			}			
		}else{
			alert('Faltan campos por llenar.');
		}
			
	});

	
	function validar_linea(){
		var ok;

		if ( $('#combobox_cuentas').val() != '' ) {
			var tercero = '<span style="color:white;">' + $('#combobox_terceros').val() + '-</span>' + $( "#combobox_terceros option:selected" ).text();

			var detalle = $('#col_detalle').val();

			var debito = $('#col_debito').val();
			
			if ( debito == '' ) {
				debito = 0;
				var credito = $('#col_credito').val();
				if ( credito == '' ) {

					alert('Debe ingresar un valor Débito o Crédito');
					$('#col_debito').focus();
					ok = false;
				}else{
					if ( $.isNumeric(credito)  && credito > 0 ) {
						ok = true;
					}else{
						$('#col_credito').attr('style','background-color:#FF8C8C;');
						$('#col_credito').focus();
						ok = false;
					}	
				}
			}else{
				credito = 0;
				if ( $.isNumeric(debito) && debito > 0 ) {
					ok = true;
				}else{
					$('#col_debito').attr('style','background-color:#FF8C8C;');
					$('#col_debito').focus();
					ok = false;
				}
			}
		}else{
			alert('Debe seleccionar una cuenta.');
			$('#combobox_cuentas').focus();
			ok = false;
		}
		return ok;
	}

	function calcular_totales(){
		var sum = 0.0;

		// Sumar columna de los débitos
		sum = 0.0;
		$('.debito').each(function()
		{
		    var cadena = $(this).text();
		    sum += parseFloat( cadena.substring(1) );
		});
		$('#total_debito').text("$"+sum.toFixed(2));
		sumas_iguales = sum;

		// Sumar columna de los créditos
		sum = 0.0;
		$('.credito').each(function()
		{
		    var cadena = $(this).text();
		    sum += parseFloat( cadena.substring(1) );
		});
		$('#total_credito').text("$"+sum.toFixed(2));


		sumas_iguales = sumas_iguales - sum;
		$('#sumas_iguales').text( sumas_iguales );
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

} );