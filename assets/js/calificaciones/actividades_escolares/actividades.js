
	var URL_SITE = "{{ url('/') }}";

	$(document).ready(function(){

	var datos_registro, url;

	var direccion = location.href;

	$('#url_recurso').parent('div').parent('div').hide();
	$('#div_archivo_adjunto').hide();

	// Si se está en la url de editar 
	if( direccion.search("edit") >= 0 ) 
	{
		// Llenar select de asignaturas con AJAX y seelccionar la asignatura almacenada
		var curso_id = $("#curso_id").val();
		datos_registro = JSON.parse( $("#datos_registro").val() );
		url = '../../get_select_asignaturas/' + curso_id;
		llenar_select_asignaturas( datos_registro, url);

		// Mostar u ocultar campos de acuerdo al valor almacenado en tipo de recurso
		switch( $('#tipo_recurso').val() )
		{
			case 'Adjunto':
				$('#div_archivo_adjunto').show();
				$('#div_archivo_adjunto').append('<label>'+datos_registro.archivo_adjunto+'</label>');
			break;
			default:
				$('#url_recurso').parent('div').parent('div').show();
				$('#url_recurso').val( datos_registro.url_recurso );
			break;
		}

		//alert('¡¡¡ ADVERTENCIA !!! Al presionar el botón Guardar, se borrarán todas las respuestas ingresadas por los estudiantes para esta actividad.');
	}

	$("#curso_id").on('change', function(){
		// Si se está en la url de editar 
		if( direccion.search("edit") >= 0 ) 
		{
			alert('¡¡¡ ADVERTENCIA !!! Al cambiar el curso se borrarán todas las respuestas ingresadas por los estudiantes para esta actividad.');
		}
	});

	CKEDITOR.replace('instrucciones', {
	    height: 200,
	      // By default, some basic text styles buttons are removed in the Standard preset.
	      // The code below resets the default config.removeButtons setting.
	      removeButtons: ''
	    });

	ajustar_opciones_select();
	

	$('#tipo_recurso').change(function()
	{
		// Se ocultan controles y se quitan los valores
		$('#url_recurso').parent('div').parent('div').hide();
		$('#url_recurso').val(' ');
		$('#div_archivo_adjunto').hide();
		$('#archivo_adjunto').val( '' );

		// Según la opción se muestran los controles
		switch( $('#tipo_recurso').val() )
		{
			case '':
			break;
			case 'Adjunto':
				$('#div_archivo_adjunto').show(500);
			break;
			default:
				$('#url_recurso').parent('div').parent('div').show(500);
			break;
		}
	});

	function llenar_select_asignaturas( datos_registro, url)
	{
		$.get( url, function( datos ) {
			$(".select_dependientes_hijo").html(datos);
			$(".select_dependientes_hijo").val( datos_registro.asignatura_id );
		});/**/
	}

	/*
	  * Se deben restringir los selects de cursos y asignaturas de acuerdo al usuario
	  */
	function ajustar_opciones_select()
	{
		if( direccion.search("edit") >= 0 ) 
		{
			URL_SITE = '../../';
		}else{
			URL_SITE = '../';
		}
		
		var vec_cursos = [];
		$.get( URL_SITE+'academico_docente/get_carga_academica/null', function( datos ) {
			
			// Se crea un array de IDs de cursos
			var i = 0;
			$.each( JSON.parse(datos), function( index, value ) {
				vec_cursos[i] = parseInt(value.curso_id);
				i++;
			});
			

			// Se rrecorren las opciones del select cursos y si la opción no está en el array vec_cursos (curso no está asignado al usuario), se elimina
			$.each( $('#curso_id option'), function(op){ 
					if ( !vec_cursos.includes( op ) ) 
					{
						$('#curso_id option[value='+op+']').remove();
					}
			});

		});		
	}
	
	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

});