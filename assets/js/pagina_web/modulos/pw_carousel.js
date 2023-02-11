$(document).ready(function(){
	
	var modelo_entidad_id = getParameterByName('id_modelo');


	$("#modelo_entidad_id").val( modelo_entidad_id );

	$("#modelo_padre_id").val(0);
	$("#registro_modelo_padre_id").val(0);	


	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

});