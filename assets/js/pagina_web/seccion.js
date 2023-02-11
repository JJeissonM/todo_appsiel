$(document).ready(function(){

	var direccion = location.href;

	$('#titulo').keyup(function(){

		var titulo = $('#titulo').val();
		
		if( direccion.search("edit") >= 0 ) 
		{
			// Editando
			var url = '../../generar_slug/' + titulo;
		}else{
			// Creando
			var url = '../generar_slug/' + titulo;
		}

		$.get( url, function( respuesta ) {
			$('#slug').val( respuesta );
		});
		
	});
});