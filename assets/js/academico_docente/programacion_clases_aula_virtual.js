
$(document).ready(function(){

	$('#fecha').val('0000-00-00');
	$('#fecha').prop('required',false);
	$('#fecha').hide();
	$('#fecha').parent().prev('label').text('');

	$('#descripcion').prop('required',false);
	$('#descripcion').hide();
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

	$("#tipo_evento").change(function (){

		switch( $(this).val() )
		{
			case 'clase_normal':
				habilitar_inputs_clase_normal();
				deshabilitar_input_fecha();
				deshabilitar_input_descripcion();
				$('#dia_semana').focus();
				break;

			case 'descanso':
				habilitar_input_descripcion();
				deshabilitar_inputs_clase_normal();
				$('#descripcion').focus();
				break;

			case 'otro':
				habilitar_input_fecha();
				habilitar_input_descripcion();
				deshabilitar_inputs_clase_normal();
				$('#descripcion').focus();
				break;

			default:
				break;
		}
	});

	function habilitar_inputs_clase_normal()
	{
		
		$('#asignatura_id').show();
		$('#asignatura_id').parent().prev('label').text('Asignatura:');

		$('#guia_academica_id').show();
		$('#guia_academica_id').parent().prev('label').text('Guía académica:');

		$('#actividad_escolar_id').show();
		$('#actividad_escolar_id').parent().prev('label').text('Actividad escolar:');

		$('#enlace_reunion_virtual').show();
		$('#enlace_reunion_virtual').parent().prev('label').text('Enlace reunión virtual:');
	}

	function deshabilitar_inputs_clase_normal()
	{
		$('#asignatura_id').hide();
		$('#asignatura_id').parent().prev('label').text('');

		$('#guia_academica_id').hide();
		$('#guia_academica_id').parent().prev('label').text('');

		$('#actividad_escolar_id').hide();
		$('#actividad_escolar_id').parent().prev('label').text('');

		$('#enlace_reunion_virtual').hide();
		$('#enlace_reunion_virtual').parent().prev('label').text('');
	}

	function habilitar_input_fecha()
	{
		$('#fecha').prop('required',true);
		$('#fecha').show();
		$('#fecha').parent().prev('label').text('*Fecha:');
	}

	function deshabilitar_input_fecha()
	{
		$('#fecha').prop('required',false);
		$('#fecha').hide();
		$('#fecha').parent().prev('label').text('');
	}

	function habilitar_input_descripcion()
	{
		$('#descripcion').prop('required',true);
		$('#descripcion').show();
		$('#descripcion').parent().prev('label').text('*Descripción:');
	}

	function deshabilitar_input_descripcion()
	{
		$('#descripcion').prop('required',false);
		$('#descripcion').hide();
		$('#descripcion').parent().prev('label').text('');
	}

	/**/

});