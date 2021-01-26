$(document).ready(function(){

	var url;

	var direccion = location.href;

	var documento_inicial = parseInt( $("#numero_identificacion").val() );

	/*
		WARNING: FALTA VALIDAR UN TERCERO DE UNA EMPRESA DIFERENTE
	*/


	//$('#numero_identificacion').on('blur',function(){
	$(document).on('blur','#numero_identificacion',function(){
		var documento = $("#numero_identificacion").val();

		/* Cuando el javascript está dentro de una vista blade se puede llamar la url de la siguiente forma:
		url = "{{ url('core/validar_numero_identificacion/') }}";*/

		
		if( direccion.search("edit") == -1) {
			url = '../core/validar_numero_identificacion/'; // crear
		}else{
			url = '../../core/validar_numero_identificacion/'; // editar
		}
		
		$.get( url + documento, function( datos ) 
		{
	        if ( datos != '') 
	        {
	        	if ( parseInt(datos) == documento_inicial ) 
	        	{
	        		// No hay problema
	        		$('#bs_boton_guardar').show();
	        		$('#email').show();
	        	}else{
	        		$('#bs_boton_guardar').hide();
	        		$('#email').hide();
	        		alert( "Ya existe una persona con ese número de documento de identidad. Cambié el número o no podrá guardar el registro." );
	        		//$('#numero_identificacion').focus();
	        	}
	        	
	        }else{
	        	// Número de identificación
	        	$('#bs_boton_guardar').show();
	        	$('#email').show();
	        }
	        
		});
	});


	/*
	$(document).on('keyup keypress', 'form input[type="text"]', function(e) {
	  if(e.which == 13) {
	    e.preventDefault();
	    //$(this).next('form input').focus();
	    return false;
	  }
	});
	*/
		
	//$('#doc_identidad').blur(function(){
	$(document).on('blur','#doc_identidad',function(){
		var documento = $("#doc_identidad").val();
		//alert(documento);
		var form = $('#form-buscar');
		var url = form.attr('action');
		$("#doc_estudiante").val(documento);
		data = form.serialize();
		$.post(url,data,function(result){					
			var vec = result.split("a3p0");
			var vec2 = vec[9].split("-");
			if(vec[0]!='9999999999'){
				// Si el estudiante existe
				$('#alert-warning').show(1000);
				$("#id").val(vec[0]);
				$("#nombres").val(vec[1]);
				$("#apellido1").val(vec[2]);
				$("#apellido2").val(vec[3]);
				$("#genero").val(vec[5]);
                $("#direccion").val(vec[6]);
                $("#barrio").val(vec[7]);
				$("#telefono").val(vec[8]);
				$("#fecha_nacimiento").val(vec2[0]+"-"+vec2[1]+"-"+vec2[2]);
                $("#ciudad_nacimiento").val(vec[10]);
                $("#papa").val(vec[11]);
                $("#ocupacion_papa").val(vec[12]);
                $("#telefono_papa").val(vec[13]);
                $("#email_papa").val(vec[14]);
                $("#mama").val(vec[15]);
                $("#ocupacion_mama").val(vec[16]);
                $("#telefono_mama").val(vec[17]);
                $("#email_mama").val(vec[18]);
            }else{
				$('#alert-warning').hide(1000);
				$("#id").val("0");			
				$("#nombres").val("");
				$("#apellido1").val("");
				$("#apellido2").val("");
				$("#genero").val("");
				$("#direccion").val("");
				$("#telefono").val("");
				$("#fecha_nacimiento").val("");
                $("#ciudad_nacimiento").val("");
                $("#papa").val("");
                $("#ocupacion_papa").val("");
                $("#telefono_papa").val("");
                $("#email_papa").val("");
                $("#mama").val("");
                $("#ocupacion_mama").val("");
                $("#telefono_mama").val("");
                $("#email_mama").val("");
			}
		});
	});



	$("#nivel_grado").change(function(){
	    $('#spin').show();
		var nivel = $(this).val();		
		var url = '../get_select_asignaturas/'+nivel;

		$.get( url, function( datos ) {
	        $('#spin').hide();
			$("#asignatura_id").html(datos);
		});
	});

	$("#nivel_grado_edit").change(function(){
	    $('#spin').show();
		var nivel = $(this).val();		
		var url = '../../get_select_asignaturas/'+nivel;

		$.get( url, function( datos ) {
	        $('#spin').hide();
			$("#asignatura_id").html(datos);
		});
	});


	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

	$(".select_dependientes_padre").change( function(){
		if( $(".select_dependientes_padre").val() !== '' ) {
			$('#div_cargando').fadeIn();
			$(".select_dependientes_hijo").html( '<option value=""> </option>');

			var id_select_padre = $(this).val();
			var id_modelo = getParameterByName('id_modelo');
			var url;

			var direccion = location.href;
			if( direccion.search("edit") == -1) {
				url = '../select_dependientes/' + id_modelo + '/' + id_select_padre;
			}else{
				url = '../../select_dependientes/' + id_modelo + '/' + id_select_padre;
			}		

			$.get( url, function( datos ) {
		        $('#div_cargando').hide();
				$(".select_dependientes_hijo").html(datos);
				$(".select_dependientes_hijo").focus();
			});
		}
	} );


	$('#btn_excel').click(function(event){
		event.preventDefault();
		var nombre_listado = $(this).attr('title');
		var tT = new XMLSerializer().serializeToString(document.querySelector('table')); //Serialised table
		var tF = nombre_listado + '.xlsx'; //Filename
		var tB = new Blob([tT]); //Blub

		if(window.navigator.msSaveOrOpenBlob){
		    //Store Blob in IE
		    window.navigator.msSaveOrOpenBlob(tB, tF)
		}
		else{
		    //Store Blob in others
		    var tA = document.body.appendChild(document.createElement('a'));
		    tA.href = URL.createObjectURL(tB);
		    tA.download = tF;
		    tA.style.display = 'none';
		    tA.click();
		    tA.parentNode.removeChild(tA)
		}
	});

	$(document).on('change','#tipo',function(){

		switch( $(this).val() )
		{
			case 'Persona natural':
				$('#razon_social').parent().parent().fadeOut();
				$('#nombre1').parent().parent().fadeIn();
				$('#otros_nombres').parent().parent().fadeIn();
				$('#apellido1').parent().parent().fadeIn();
				$('#apellido2').parent().parent().fadeIn();
				break;

			case 'Persona jurídica':
				$('#razon_social').parent().parent().fadeIn();
				$('#nombre1').parent().parent().fadeOut();
				$('#otros_nombres').parent().parent().fadeOut();
				$('#apellido1').parent().parent().fadeOut();
				$('#apellido2').parent().parent().fadeOut();	

				break;

			case 'Interno':
				$('#razon_social').parent().parent().fadeOut();
				$('#nombre1').parent().parent().fadeOut();
				$('#otros_nombres').parent().parent().fadeOut();
				$('#apellido1').parent().parent().fadeOut();
				$('#apellido2').parent().parent().fadeOut();	

				break;
				
			default:
				$('#razon_social').parent().parent().fadeIn();
				$('#nombre1').parent().parent().fadeIn();
				$('#otros_nombres').parent().parent().fadeIn();
				$('#apellido1').parent().parent().fadeIn();
				$('#apellido2').parent().parent().fadeIn();	
				break;
		}
	});
});