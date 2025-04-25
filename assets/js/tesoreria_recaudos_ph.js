$(document).ready(function(){
	
	var tercero_id;

	$('#core_tipo_doc_app_id').change(function(){
		$('#fecha').focus();
	});

	$('#btn_continuar1').click(function(event)
	{
		event.preventDefault();

		asignar_codigo_tercero();		
		
		if ( validar_requeridos() ) 
		{
			$('#div_ingreso_registros').show();
			reset_form_registro();
	        $("#myModal").modal(
	        	{backdrop: "static",keyboard: 'true'}
	        );
		}
	});

	function asignar_codigo_tercero()
	{
		tercero_id = $('#tercero_id').val().split('a3p0');
		$('#core_tercero_id').val( tercero_id[0] );
		$('#codigo_referencia_tercero').val( tercero_id[1] );
	}

	/*
	**	Abrir formulario
	*/
	$("#btn_nueva_linea").click(function(event){
		event.preventDefault();
        reset_form_registro();
        $("#myModal").modal(
        	{backdrop: "static"}
        );
    });
    	
    // Al mostrar la ventana modal
    $("#myModal,#myModal2").on('shown.bs.modal', function () {
    	$('#teso_medio_recaudo_id').focus();
    });
	// Al OCULTAR la ventana modal
    $("#myModal,#myModal2").on('hidden.bs.modal', function () {
       $('#btn_continuar2').focus();
    });

	$('#teso_medio_recaudo_id').change(function(){
		var valor = $(this).val().split('-');
		/*var texto_motivo = $( "#teso_motivo_id" ).html();//[ , $( "#teso_motivo_id option:selected" ).text() ];
		if (texto_motivo == '') {
			alert('No se han creado motivos para el TIPO DE RECAUDO selecccionado. Debe crear al menos un MOTIVO para cada TIPO DE RECAUDO. No puede continuar.');
			$('#teso_tipo_motivo').focus();
		}else{*/
			if (valor!='') {
				if (valor[1]=='Tarjeta bancaria'){
					$('#div_caja').hide();
					$('#div_cuenta_bancaria').show();
				}else{
					$('#div_cuenta_bancaria').hide();
					$('#div_caja').show();
				}
				habilitar_text($('#valor_total'));
				//$('#btn_agregar').show();
				$('#valor_total').focus();
			}else{
				$('#div_cuenta_bancaria').hide();
				$('#div_caja').hide();
				deshabilitar_text($('#valor_total'));
				$(this).focus();
			}
		//}
						
	});

	$('#valor_total').keyup(function(event){
		/**/
		var ok;
		if( $.isNumeric( $(this).val() ) ) {
			$(this).attr('style','background-color:white;');
			ok = true;
		}else{
			$(this).attr('style','background-color:#FF8C8C;');
			$(this).focus();
			ok = false;
		}

		var x = event.which || event.keyCode;
		if( x === 13 ){

			if (ok) {
				$('#btn_agregar').show();
				$('#btn_agregar').focus();
			}

		}
	});


	/*
	** Al presionar el botón agregar (ingreso de medios de recaudo)
	*/
	$('#btn_agregar').click(function(event){
		event.preventDefault();
		
		var valor_total = $('#valor_total').val();

		if($.isNumeric(valor_total) && valor_total>0)
		{		
		
			var medio_recaudo = $( "#teso_medio_recaudo_id" ).val().split('-');
			var texto_medio_recaudo = [ medio_recaudo[0], $( "#teso_medio_recaudo_id option:selected" ).text() ];
			//console.log(medio_recaudo);
			if (medio_recaudo[1] == 'Tarjeta bancaria'){
				var texto_caja = [0,''];
				var texto_cuenta_bancaria = [ $('#teso_cuenta_bancaria_id').val(), $('#teso_cuenta_bancaria_id option:selected').text() ];
			}else{
				var texto_cuenta_bancaria = [0,''];
				var texto_caja = [ $('#teso_caja_id').val(), $('#teso_caja_id option:selected').text() ];
			}

			var texto_motivo = [ $( "#teso_motivo_id" ).val(), $( "#teso_motivo_id option:selected" ).text() ];
			

			var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";


			celda_valor_total = '<td class="valor_total">$'+valor_total+'</td>';

			$('#ingreso_registros').find('tbody:last').append('<tr>'+
															'<td><span style="color:white;">'+texto_medio_recaudo[0]+'-</span><span>'+texto_medio_recaudo[1]+'</span></td>'+
															'<td><span style="color:white;">'+texto_motivo[0]+'-</span><span>'+texto_motivo[1]+'</span></td>'+
															'<td><span style="color:white;">'+texto_caja[0]+'-</span><span>'+texto_caja[1]+'</span></td>'+
															'<td><span style="color:white;">'+texto_cuenta_bancaria[0]+'-</span><span>'+texto_cuenta_bancaria[1]+'</span></td>'+
															celda_valor_total+
															'<td>'+btn_borrar+'</td>'+
															'</tr>');
			
			// Se calculan los totales para la última fila
			calcular_totales();
			reset_form_registro();
			
			deshabilitar_campos_form_create();
			$('#btn_continuar1').hide();

			$('#btn_continuar2').show();

		}else{

			$('#valor_total').attr('style','background-color:#FF8C8C;');
			$('#valor_total').focus();

			alert('Datos incorrectos o incompletos. Por favor verifique.');

			if ( $('#total_valor_total').text() == '0' ) 
			{
				$('#btn_continuar2').hide();
			}
		}
	});

	/*
	** Al eliminar una fila
	*/
	// Se utiliza otra forma con $(document) porque el $('#btn_eliminar') no funciona pues
	// es un elemento agregadi despues de que se cargó la página
	$(document).on('click', '.btn_eliminar', function(event) {
		event.preventDefault();
		var fila = $(this).closest("tr");
		fila.remove();
		calcular_totales();
		if ( $('#total_valor_total').text() == '0' ) 
		{
			$('#btn_continuar2').hide();
			habilitar_campos_form_create();
			$('#btn_continuar1').show();
			$('#div_ingreso_registros').hide();
		}
	});

	$('#btn_continuar2').click(function(event){
		event.preventDefault();
		
		asignar_codigo_tercero();
		

		if ( validar_requeridos() ) 
		{
			//console.log( $('#teso_tipo_motivo').val() );
			if ( $('#teso_tipo_motivo').val() == 'recaudo-cartera') {
				
				$('#div_cargando').show();
						
				var url = '../../cxc/get_cartera_inmueble/' + tercero_id[1] + '/' + $('#fecha').val();
				$.get( url, function( datos ) {
			        $('#div_cargando').hide();
			        //console.log(datos);

			        $('#btn_continuar2').hide();
			        $('#btn_nueva_linea').hide();
			        $('#btn_cancelar1').show();
			        $('.btn_eliminar').hide();


					$('#div_aplicacion_cartera').show();
					// se pasa el total de los ingresos de recaudos a la casilla pendiente x aplicar
					var total = $('#total_valor_total').text();
					//console.log( document.getElementById("pendiente_aplicar") );
					//document.getElementById("pendiente_aplicar").innerHTML = parseFloat(total);
					
					$('#div_cartera').html(datos);
					$('#pendiente_aplicar').val( parseFloat(total) );

					$("#div_cartera input:text").first().focus();
				});
			}else{
				$('#btn_continuar2').hide();
				$('#btn_cancelar1').show();
		        $('#btn_nueva_linea').hide();
		        $('.btn_eliminar').hide();
		        $('#btn_guardar2').show();
		        $('#btn_guardar2').focus();
			}
		}
	});

	$('#aplicar_cartera').click(function(event){

		event.preventDefault();
			
		$('#div_cargando').show();
		
		asignar_codigo_tercero();

		$('#div_tercero_id_aux').show();
		$('#tercero_id_aux').html( $('#tercero_id').html() );
		$('#tercero_id_aux').val( $('#tercero_id').val() );

		alert( $('#tercero_id_aux').val() );
				
		var url = '../../cxc/get_cartera_inmueble/' + tercero_id[1] + '/' + $('#fecha').val();
		$.get( url, function( datos ) {
	        $('#div_cargando').hide();
	        //console.log(datos);

	        $('#btn_continuar2').hide();
	        $('#btn_nueva_linea').hide();
	        $('#btn_cancelar1').show();
	        $('.btn_eliminar').hide();


			$('#div_aplicacion_cartera').show();
			// se pasa el total de los ingresos de recaudos a la casilla pendiente x aplicar
			var total = $('#total_valor_total').text();
			//console.log( document.getElementById("pendiente_aplicar") );
			//document.getElementById("pendiente_aplicar").innerHTML = parseFloat(total);
			
			$('#div_cartera').html(datos);
			$('#pendiente_aplicar').val( parseFloat(total) );

			$("#div_cartera input:text").first().focus();
		});
	});



	$('#btn_cancelar1').click(function(event){
		event.preventDefault();
		$('#div_cartera').html('');

		//Se resetean las filas del listado de pendiente por aplicar
		$('#documentos_a_cancelar').find('tbody').html( '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>' );
		
		$('#btn_continuar2').hide();
        $('#btn_nueva_linea').show();
        $('#btn_cancelar1').hide();
        $('.btn_eliminar').show();
        $('#div_aplicacion_cartera').hide();
        $('#div_documentos_a_cancelar').hide();
        $('#btn_guardar').hide();
		$('#btn_guardar2').hide();

        $('#ingreso_registros').find('tbody').html( '<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>' );
        $('#total_valor_total').text( '0' );
		$('#total_valor_aux').text( '$0.00' );
		$('#div_ingreso_registros').hide();
		$('#btn_continuar1').show();
        habilitar_campos_form_create();
	});

	
	$(document).on('keyup', '.text_aplicar', function() {
		var celda = $(this);
		//console.log( celda );
		validar_valor_aplicar( celda );

		var x = event.which || event.keyCode;
		if( x === 13 ){
			celda.next('input:button').focus();
		}
	});


	$(document).on('click', '.btn_agregar_documento', function(event) {
		event.preventDefault();

		// Se obtiene la fila del botón donde se ha hecho clic 
		var fila = $(this).closest("tr");

		// Se obtiene el control caja de texto de la fila
		var celda = fila.find("input:text");

		// Se valida el valor de la caja de texto
		// Si es un número y es menor que el saldo pendiente
		if( validar_valor_aplicar( celda ) ){

			var celda_borrar = "<td> &nbsp; </td>";
			var valor = celda.val();
			fila.find("td:last").text( valor );

			if ( celda.attr('id') == 'pendiente_aplicar' ) 
			{
				fila.prepend( "<td style='trasnparent: white;'> A </td>" );
				$('#btn_cancelar1').show();
				$('#btn_guardar').show();
				$('#btn_guardar').focus();
			}else{

				var id_encabezado_documento = fila.attr('id');

				fila.prepend( "<td style='trasnparent: white;'> " + id_encabezado_documento + " </td>" );
				
				var pendiente_aplicar = parseFloat( $('#pendiente_aplicar').val() );
				pendiente_aplicar = pendiente_aplicar - parseFloat( valor );
				$('#pendiente_aplicar').val(pendiente_aplicar);

				if( pendiente_aplicar == 0 ) {
					$('#btn_cancelar1').show();
					$('#btn_guardar').show();
					$('#btn_guardar').focus();
				}else{
					$("#div_cartera input:text").first().focus();
				}
			}

			fila.append( celda_borrar );

			$('#div_documentos_a_cancelar').show();
			$('#documentos_a_cancelar').find('tbody:last').append( fila );

		}

	});


	// Para guardar recaudos de cartera
	$('#btn_guardar').click(function(event){
		event.preventDefault();

		asignar_codigo_tercero();
		
		// Se asigna la tabla de ingreso de medios de recaudo a un campo hidden
		var valores_recaudo = $('#ingreso_registros').tableToJSON();
		$('#tabla_valores_recaudo').val(JSON.stringify(valores_recaudo));

		// Se asigna la tabla de documentos selecccionados para cancelación a un campo hidden
		var documentos_a_cancelar = $('#documentos_a_cancelar').tableToJSON();
		$('#tabla_documentos_a_cancelar').val(JSON.stringify(documentos_a_cancelar));

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
			habilitar_campos_form_create();
			$('#form_create').submit();
			
		}else{
			alert('Faltan campos por llenar.');
		}			
	});

	// Para guardar anticipos o otros recaudos
	$('#btn_guardar2').click(function(event){
		event.preventDefault();

		asignar_codigo_tercero();
		
		// Se asigna la tabla de ingreso de medios de recaudo a un campo hidden
		var valores_recaudo = $('#ingreso_registros').tableToJSON();
		$('#tabla_valores_recaudo').val(JSON.stringify(valores_recaudo));

		// Se asigna la tabla de documentos selecccionados para cancelación a un campo hidden
		$('#tabla_documentos_a_cancelar').val( 'No' );

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
			habilitar_campos_form_create();
			$('#form_create').submit();
			
		}else{
			alert('Faltan campos por llenar.');
		}			
	});

	
	function validar_valor_aplicar(celda){

		if ( celda.attr('id') == 'pendiente_aplicar' ) 
		{
			ok = true;
		}else{
			var fila = celda.closest("tr");
			//console.log(fila);

			var ok;

			var valor = celda.val();

			if( $.isNumeric( valor ) ){
				valor = parseFloat( valor );
				//console.log(valor);
			}		
			
			var pendiente_aplicar = parseFloat( $('#pendiente_aplicar').val() );

			var saldo_pendiente = fila.find('td.col_saldo_pendiente').text();
			saldo_pendiente = parseFloat( saldo_pendiente.replace( /\./g, "" ) );

			//console.log('valor: ' + (valor) + ' pendiente_aplicar: ' + (pendiente_aplicar) + ' saldo_pendiente: ' + (saldo_pendiente) );
			
			//console.log('condicion 2: ' + (valor <= pendiente_aplicar) + ' condicion 3: ' + (valor <= saldo_pendiente) );


			if( $.isNumeric( valor ) && (valor <= pendiente_aplicar) && (valor <= saldo_pendiente) && (valor > 0) ) {
				celda.attr('style','background-color:white;');
				ok = true;
			}else{
				celda.attr('style','background-color:#FF8C8C;');
				celda.focus();
				ok = false;
			}
		}
		return ok;
	}

	function habilitar_text($control){
		$control.removeAttr('disabled');
		$control.attr('style','background-color:white;');
	}

	function deshabilitar_text($control){
		$control.attr('style','background-color:#ECECE5;');
		$control.attr('disabled','disabled');
	}

	function reset_form_registro(){
		
		var url = '../../tesoreria/ajax_get_motivos/'+$('#teso_tipo_motivo').val();
		$.get( url, function( datos ) {
	        $('#teso_motivo_id').html(datos);
		});

		$('#form_registro input[type="text"]').val('');

		$('#form_registro input[type="text"]').attr('style','background-color:#ECECE5;');
		$('#form_registro input[type="text"]').attr('disabled','disabled');

		$('#div_caja').hide();
		$('#div_cuenta_bancaria').hide();

		$('#btn_agregar').hide();

		$('#teso_medio_recaudo_id').val('');
		$('#teso_medio_recaudo_id').focus();
	}

	function calcular_totales(){
		var sum = 0.0;
		sum = 0.0;
		$('.valor_total').each(function()
		{
		    var cadena = $(this).text();
		    sum += parseFloat(cadena.substring(1));
		});

		$('#total_valor_total').text( sum );
		$('#total_valor_aux').text( "$" + new Intl.NumberFormat("de-DE").format( sum ) );
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

	function deshabilitar_campos_form_create()
	{

		// Se cambia el de name al campo core_tercero_id, pues como está desabilitado 
		// el SUBMIT del FORM no lo envía en el REQUEST
		$('#fecha').attr('disabled','disabled');

		// se oculta la caja de texto del tercero y se muestra el select real
		$('.custom-combobox').hide();
		$('#core_tercero_id_no').show();
		$('#core_tercero_id_no').attr('disabled','disabled');

		$('#tercero_id').show();
		$('#tercero_id').attr('disabled','disabled');


		$('#teso_tipo_motivo').attr('disabled','disabled');
	}

	function habilitar_campos_form_create()
	{
		$('#fecha').removeAttr('disabled');
		
		$('.custom-combobox').show();
		$('#core_tercero_id_no').hide();
		$('#tercero_id').hide();

		$('#core_tercero_id_no').removeAttr('disabled');
		
		$('#tercero_id').removeAttr('disabled');

		$('#teso_tipo_motivo').removeAttr('disabled');
	}
});