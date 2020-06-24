$(document).ready(function(){

	var direccion = location.href;

	CKEDITOR.replace('contenido_articulo', {
	    height: 200,
	      // By default, some basic text styles buttons are removed in the Standard preset.
	      // The code below resets the default config.removeButtons setting.
	      removeButtons: ''
	    });

    //$('#alias_sef_no').attr('disabled','disabled');
	
	$('#titulo').keyup(function(){
		//$('#div_cargando').show();

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
			//$('#div_cargando').hide();
			$('#slug').val( respuesta );
		});
		
	});
});