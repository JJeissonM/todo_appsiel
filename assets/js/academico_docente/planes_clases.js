
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
		    toolbar: [{
		          name: 'clipboard',
		          items: ['PasteFromWord', '-', 'Undo', 'Redo']
		        },
		        {
		          name: 'basicstyles',
		          items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'Subscript', 'Superscript']
		        },
		        {
		          name: 'links',
		          items: ['Link', 'Unlink']
		        },
		        {
		          name: 'paragraph',
		          items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']
		        },
		        {
		          name: 'insert',
		          items: ['Image', 'Table']
		        },
		        {
		          name: 'editing',
		          items: ['Scayt']
		        },
		        '/',

		        {
		          name: 'styles',
		          items: ['Format', 'Font', 'FontSize']
		        },
		        {
		          name: 'colors',
		          items: ['TextColor', 'BGColor', 'CopyFormatting']
		        },
		        {
		          name: 'align',
		          items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
		        },
		        {
		          name: 'document',
		          items: ['Print', 'Source']
		        }
		      ],

		      // Enabling extra plugins, available in the full-all preset: https://ckeditor.com/cke4/presets
		      extraPlugins: 'colorbutton,font,justify,print,tableresize,pastefromword,liststyle',
		      removeButtons: '',
			  height: 200
	    });

	});

	$('.contenido').on( 'blur', function(){

		$(this).attr('name','elemento_descripcion[]');

	});	

});