
$(document).ready(function(){

	$('#fecha').val('0000-00-00');
	$('#fecha').prop('required',false);
	//$('#fecha').hide();
	$('#fecha').parent().prev('label').text('');

	$('#descripcion').prop('required',false);
	//$('#descripcion').hide();
	$('#descripcion').parent().prev('label').text('');

	
	$("#asignatura_id").change(function () {

		if ( $(this).val() == '' )
		{
			return false;
		}

		$('#div_cargando').fadeIn();
		$('#guia_academica_id').html('<option value=""> </option>');
		$('#actividad_escolar_id').html('<option value=""> </option>');

		var direccion = location.href;
		if( direccion.search("edit") == -1)
		{
			// Vista Create
			var url = '../sga_get_options_guias_academicas/' + $('#curso_id').val() + '/' + $('#asignatura_id').val() + '/' + $('#user_id').val();
		} else {
			// Vista Edit
			var url = '../../sga_get_options_guias_academicas/' + $('#curso_id').val() + '/' + $('#asignatura_id').val() + '/' + $('#user_id').val();
		}

		$.get(url, function (datos) {
			$('#div_cargando').hide();
			$("#guia_academica_id").html(datos);

			$('#div_cargando').fadeIn();

			var direccion = location.href;
			if( direccion.search("edit") == -1)
			{
				// Vista Create
				var url = '../sga_get_options_actividades_escolares/' + $('#curso_id').val() + '/' + $('#asignatura_id').val() + '/' + $('#user_id').val();
			} else {
				// Vista Edit
				var url = '../../sga_get_options_actividades_escolares/' + $('#curso_id').val() + '/' + $('#asignatura_id').val() + '/' + $('#user_id').val();
			}

			$.get(url, function (datos) {
				$('#div_cargando').hide();
				$("#actividad_escolar_id").html(datos);
			});

		});
	});
	/**/

});