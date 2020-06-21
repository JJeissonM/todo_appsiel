var URL_SITE = "{{ url('/') }}";

$(document).ready(function(){

	var datos_registro, url;

	var direccion = location.href;

	// Si se está en la url de editar 
	if( direccion.search("edit") >= 0 ) 
	{
		// Llenar select de asignaturas con AJAX y seelccionar la asignatura almacenada
		var contab_cuenta_clase_id = $("#contab_cuenta_clase_id").val();
		datos_registro = JSON.parse( $("#datos_registro").val() );
		url = '../../contab_get_grupos_cuentas/' + contab_cuenta_clase_id;
		llenar_select_hijo( datos_registro, url);
	}


	function llenar_select_hijo( datos_registro, url)
	{
		$.get( url, function( datos ) {
			$(".select_dependientes_hijo").html(datos);
			$(".select_dependientes_hijo").val( datos_registro.contab_cuenta_grupo_id );
			if( typeof datos_registro.grupo_padre_id !== typeof undefined){
				$(".select_dependientes_hijo").val( datos_registro.grupo_padre_id );
			}
			
		});/**/
	}

	
	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

});