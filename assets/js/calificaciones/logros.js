var URL_SITE = "{{ url('/') }}";

$(document).ready(function(){

	var datos_registro, url;

	var direccion = location.href;


	$('#curso_id').on('change',function(){
		$('#asignatura_id').html('<option value="">Seleccionar...</option>');
		$('#escala_valoracion_id').html('<option value="">Seleccionar...</option>');
		$('#asignatura_id').focus();
	});

	$('#periodo_id').on('change',function(){
		var periodo_id = $("#periodo_id").val();
		var curso_id = $("#curso_id").val();
		var asignatura_id = $("#asignatura_id").val();

		if ( periodo_id == '')
		{
			return false;
		}

		if ( curso_id == '')
		{
			return false;
		}

		if ( asignatura_id == '')
		{
			return false;
		}

		
		// Llenar select de escala_valoracion con AJAX
		$('#div_cargando').fadeIn();

		url = '../get_select_escala_valoracion/' + periodo_id + '/' + curso_id + '/' + asignatura_id;

		// Para la Aplicación de Académico Docente
		if( direccion.search("academico_docente") >= 0 ) 
		{
			url = '../../../get_select_escala_valoracion/' + periodo_id + '/' + curso_id + '/' + asignatura_id;
		}
		
		$.get( url, function( datos ) {
			$("#escala_valoracion_id").html(datos);
			$('#div_cargando').hide();
			$('#escala_valoracion_id').focus();
		});
	});

	$('#asignatura_id').on('change',function(){
		$('#div_cargando').fadeIn();
		// Llenar select de escala_valoracion con AJAX
		var periodo_id = $("#periodo_id").val();
		var curso_id = $("#curso_id").val();
		var asignatura_id = $("#asignatura_id").val();

		if ( curso_id == '')
		{
			$("#curso_id").focus();
			$(this).val('');
			alert('Debe seleccionar un curso.');
		}

		if ( periodo_id == '')
		{
			$("#periodo_id").focus();
			$(this).val('');
			alert('Debe seleccionar un periodo.');
		}

		url = '../get_select_escala_valoracion/' + periodo_id + '/' + curso_id + '/' + asignatura_id;

		$.get( url, function( datos ) {
			$("#escala_valoracion_id").html(datos);
			$('#div_cargando').hide();
			$('#escala_valoracion_id').focus();
		});
	});



	// Si se está en la url de editar 
	if( direccion.search("edit") >= 0 ) 
	{
		// Llenar select de asignaturas con AJAX y seelccionar la asignatura almacenada
		var curso_id = $("#curso_id").val();
		datos_registro = JSON.parse( $("#datos_registro").val() );
		//url = '../../../get_select_asignaturas/' + curso_id;
		url = '../../get_select_asignaturas/' + curso_id;
		llenar_select_asignaturas( datos_registro, url);
	}


	function llenar_select_asignaturas( datos_registro, url)
	{
		$.get( url, function( datos ) {
			$(".select_dependientes_hijo").html(datos);
			$(".select_dependientes_hijo").val( datos_registro.asignatura_id );
		});/**/
	}

	
	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

});