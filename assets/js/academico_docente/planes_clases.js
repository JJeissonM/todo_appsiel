
$(document).ready(function(){

	var direccion = location.href;
	/*
	  * Cuando se está editando un registro
	*/
	if( direccion.search("edit") >= 0 ) 
	{
		var obj = JSON.parse( $('#datos_registro').val() );

		if( obj.archivo_adjunto != '' )
		{
			$('#archivo_adjunto').hide();
			$("#archivo_adjunto").after('<div style="color:red;"> Este plan de clases ya tiene un archivo adjunto. Para adjuntar un nuevo archivo, primero debe remover el anterior.  </div>');
		}
	}


	$('.contenido').on( 'focus', function(){

		$(this).attr('name','contenido');

		CKEDITOR.replace('contenido', {
	    height: 200,
	      // By default, some basic text styles buttons are removed in the Standard preset.
	      // The code below resets the default config.removeButtons setting.
	      removeButtons: '',
	      //filebrowserUploadUrl: '../../carga_imagen_ckeditor'
	    });

	});

	$('.contenido').on( 'blur', function(){

		$(this).attr('name','elemento_descripcion[]');

	});	

});