$(document).ready(function(){

	var direccion = location.href;
	var valor_pension_mensual = 0;
	
	// Si se está en la url de editar 
	if( direccion.search("edit") >= 0 ) 
	{
		valor_pension_mensual = $('#valor_pension_anual').val() / $('#numero_periodos').val();
	}else{
		$('#numero_periodos').val(10);
		$('#fecha_inicio').val( get_fecha_hoy() );
	}

	$('#numero_periodos').attr('min','1');	

	$('#valor_pension_mensual').attr('style','display:none;');
	$('#valor_pension_mensual').after('<div id="div_valor_pension_mensual" style="border-bottom: 1px solid #ddd; height: 38px; color: #666666; font-size: 17px; padding-top: 12px;"> ' + '$ ' + new Intl.NumberFormat("de-DE").format( valor_pension_mensual.toFixed(2) ) + ' </div>');

	$('#numero_periodos').on( 'keyup', function(){

		calcular_valor_pension_mensual();
		
	});

	$('#numero_periodos').on( 'change', function(){

		calcular_valor_pension_mensual();
		
	});

	$('#valor_pension_anual').keyup( function(){

		calcular_valor_pension_mensual();
		
	});

	function calcular_valor_pension_mensual()
	{
		var valor_pension_anual = parseFloat( $('#valor_pension_anual').val() );
		var numero_periodos = parseFloat( $('#numero_periodos').val() );

		if ( numero_periodos <= 0 )
		{
			return false;
		}

		if ( isNaN(valor_pension_anual) )
		{
			$('#valor_pension_anual').attr('style', 'background-color:#FF8C8C;');
			$('#valor_pension_anual').focus();
			return false;
		}

		if ( !validar_input_numerico( $('#valor_pension_anual') ) && valor_pension_anual <= 0 )
		{
			return false;
		}

		$('#valor_pension_anual').attr('style', 'background-color:transparent;');

		var valor_pension_mensual = valor_pension_anual / $('#numero_periodos').val();

		$('#valor_pension_mensual').val( valor_pension_mensual );

		$('#div_valor_pension_mensual').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_pension_mensual.toFixed(2) ) );
	}

});