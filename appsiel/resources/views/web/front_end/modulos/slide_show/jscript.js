var cant_imagenes = 0;

$(document).ready(function(){
	
	$("#btn_nueva_linea").on('click',function(e){
		e.preventDefault();

		var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";

		cant_imagenes++;
		var id = "imgSalida_"+cant_imagenes;

		$('#ingreso_registros').find('tbody:last').append('<tr>'+
													'<td><input name="imagenes[]" type="file" class="file-input" accept="image/*"> <br> <img id="'+id+'" height="150px" src="" /> </td>'+
													'<td> <input type="text" class="form-control" name="textos_imagenes[]"> </td>'+
													'<td> <input type="text" class="form-control" name="enlaces_imagenes[]"> </td>'+
													'<td>'+btn_borrar+'</td>'+
													'</tr>');


		$(".file-input").focus();
	});


	$(document).on('click', '.btn_eliminar', function(event) {
		event.preventDefault();
		var fila = $(this).closest("tr");
		fila.remove();
	});

	$(document).on('change', '.file-input', function(e) {
		var file = e.target.files[0];
		if ( file.size < 2106000 ) {
	      	addImage(e);
	      	$(this).hide();
	      }else{
	      	alert('El peso de la imagen supera el maximo permitido.');
	      	$(this).val('');
	      }
     });

     function addImage(e){

      var file = e.target.files[0],
      imageType = /image.*/;
    
      if (!file.type.match(imageType))
       return;
  
      var reader = new FileReader();
      reader.onload = fileOnload;
      reader.readAsDataURL(file);
     }
  
     function fileOnload(e) {
      var result=e.target.result;
      $('#imgSalida_'+cant_imagenes).attr("src",result);
     }

});