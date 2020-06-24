$(document).ready(function(){

	$(document).on('keyup keypress', 'form input[type="text"]', function(e) {
	  if(e.which == 13) {
	    e.preventDefault();
	    return false;
	  }
	});

	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

	$(".select_dependientes_padre").change( function(){
		if( $(".select_dependientes_padre").val() !== '' ) {
			$('#div_cargando').fadeIn();
			$(".select_dependientes_hijo").html( '<option value=""> </option>');

			var id_select_padre = $(this).val();
			var id_modelo = getParameterByName('id_modelo');
			var url;

			var direccion = location.href;
			if( direccion.search("edit") == -1) {
				url = '../select_dependientes/' + id_modelo + '/' + id_select_padre;
			}else{
				url = '../../select_dependientes/' + id_modelo + '/' + id_select_padre;
			}		

			$.get( url, function( datos ) {
		        $('#div_cargando').hide();
				$(".select_dependientes_hijo").html(datos);
				$(".select_dependientes_hijo").focus();
			});
		}
	} );




});