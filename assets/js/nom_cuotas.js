$(document).ready(function(){
	$('#valor_acumulado').attr('disabled','disabled');

	var valor_old = $('#tope_maximo').val();

	$('#tope_maximo').keyup( function(){		

		if ( parseFloat($(this).val()) <  parseFloat($('#valor_acumulado').val()) ) 
		{
			alert('El valor del tope máximo es menor al valor acumulado. No se puede modificar.')
			$(this).val( valor_old );
		}
		
	});

});