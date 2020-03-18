
$(document).ready(function(){

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