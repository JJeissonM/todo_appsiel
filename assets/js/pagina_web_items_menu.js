$(document).ready(function(){
	//$('#enlace').attr('readonly',true);
	$('#enlace').parent('div').hide();
	$('#enlace').parent().parent().hide();

	$('#url').removeAttr('required');

	switch( $('#tipo_enlace').val() )
	{
			case 'Mostrar un artículo':

				$('#pw_articulo_id').parent('div').parent('div').show(500);
				$('#pw_articulo_id').val( $(this).val().substr(5) );
				
				$('#pw_categoria_id').parent('div').parent('div').hide();
				$('#url').parent('div').parent('div').hide();
			break;
			case 'Mostrar componente':
				$('#pw_articulo_id').parent('div').parent('div').hide();

				$('#pw_categoria_id').parent('div').parent('div').show(500);
				$('#pw_categoria_id').val( $(this).val().substr(10) );

				$('#url').parent('div').parent('div').hide();
			break;
			case 'URL personalizada':
				$('#pw_articulo_id').parent('div').parent('div').hide();
				$('#pw_categoria_id').parent('div').parent('div').hide();

				$('#url').parent('div').parent('div').show(500);
				$('#url').val( $('#enlace').val() );
			break;
			default:
				$('#pw_articulo_id').parent('div').parent('div').hide();
				$('#pw_categoria_id').parent('div').parent('div').hide();
				$('#url').parent('div').parent('div').hide();
			break;
		}

	var valor_old = $('#tope_maximo').val();

	$('#tipo_enlace').on('change', function(){

		switch( $(this).val() ){
			case 'Mostrar un artículo':
				$('#pw_articulo_id').parent('div').parent('div').show(500);
				$('#pw_categoria_id').parent('div').parent('div').hide();
				$('#url').parent('div').parent('div').hide();
			break;
			case 'Mostrar componente':
				$('#pw_articulo_id').parent('div').parent('div').hide();
				$('#pw_categoria_id').parent('div').parent('div').show(500);
				$('#url').parent('div').parent('div').hide();
			break;
			case 'URL personalizada':
				$('#pw_articulo_id').parent('div').parent('div').hide();
				$('#pw_categoria_id').parent('div').parent('div').hide();
				$('#url').parent('div').parent('div').show(500);
			break;
			default:
			break;
		}
		
	});


	$('#pw_articulo_id').on('change', function(){
		$('#enlace').val( 'blog/'+$(this).val() );
		$('#url').val( 'blog/'+$(this).val() );
	});

	$('#pw_categoria_id').on('change', function(){
		$('#enlace').val( 'categoria/'+$(this).val() );
		$('#url').val( 'categoria/'+$(this).val() );
	});

	$('#url').on('keyup', function(){
		$('#enlace').val( $(this).val() );
	});

});