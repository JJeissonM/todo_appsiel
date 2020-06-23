$(document).ready(function(){

	$('#tipo_enlace').on('change', function(){

		switch( $(this).val() ){
			case 'mostrar_articulo':
				$('#pw_articulo_id').attr('required', 'required');
				$('#url').removeAttr('required');
				break;

			case 'url_externa':
				$('#url').attr('required', 'required');
				$('#pw_articulo_id').removeAttr('required');
				break;

			default:
				$('#url').removeAttr('required');
				$('#pw_articulo_id').removeAttr('required');
				break;
		}
		
		asignar_slug();

	});


	$(document).on('change','#seccion_id, #pw_articulo_id', function(){
		
		asignar_slug();
		
	});

	function asignar_slug()
	{
		switch( $('#tipo_enlace').val() )
		{
			case 'mostrar_articulo':
				var slug = $('#pw_articulo_id').val().split('a3p0');
				break;

			case 'mostrar_seccion':
				var slug = $('#seccion_id').val().split('a3p0');
				break;

			default:
				$('#slug_id').val( 0 );
				$('#slug').val( 0 );
				return false;
				break;
		}

		$('#slug_id').val( slug[0] );
		$('#slug').val( slug[1] );

	}

});