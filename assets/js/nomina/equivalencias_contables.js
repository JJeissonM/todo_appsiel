var URL_SITE = "{{ url('/') }}";

$(document).ready(function(){

	var datos_registro, url;

	var direccion = location.href;

	$('#core_tercero_id').parent('div').prev('label').hide();

	$(document).on('change','#tercero_movimiento',function(){
		if ($(this).val() == 'tercero_especifico')
		{
			$('#core_tercero_id').parent('div').prev('label').fadeIn(500);
			$('#core_tercero_id').fadeIn(500);
		}else{
			$('#core_tercero_id').parent('div').prev('label').hide();
			$('#core_tercero_id').hide();
		}
	});

	// Si se está en la url de editar 
	if( direccion.search("edit") >= 0 ) 
	{
		if ( $('#tercero_movimiento').val() == 'tercero_especifico' )
		{
			$('#core_tercero_id').parent('div').prev('label').show();
			$('#core_tercero_id').show();
		}else{
			$('#core_tercero_id').parent('div').prev('label').hide();
			$('#core_tercero_id').hide();
		}
	}

});