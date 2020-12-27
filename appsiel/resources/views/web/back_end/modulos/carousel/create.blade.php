@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
	<style type="text/css">
		#div_cargando{
			display: none;/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom:0px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index:0;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>

		    @if( isset($url_action) )
		    	{{ Form::open(['url'=>$url_action,'id'=>'form_create','files' => true]) }}
			@else
				{{ Form::open(['url'=>'web','id'=>'form_create','files' => true]) }}
			@endif
		
		
					<?php
						$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
					  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';
					?>

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<label for="descripcion" class="col-md-3">*Descripción</label>
							<input class="col-md-9" id="descripcion" style="border: none;border-color: transparent;border-bottom: 1px solid gray;" placeholder="*Descripción" required="required" name="descripcion" type="text">
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<label for="altura_maxima" class="col-md-3">*Altura máxima</label>							
							<input class="col-md-9" id="altura_maxima" style="border: none;border-color: transparent;border-bottom: 1px solid gray;" placeholder="*Altura máxima" required="required" name="altura_maxima" type="text">
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<label for="estado" class="col-md-3">Estado</label>
							<select id="estado" style="border: none;border-color: transparent;border-bottom: 1px solid gray;" class="col-md-9" name="estado"><option value="Activo" selected="selected">Activo</option><option value="Inactivo">Inactivo</option></select>
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<label for="activar_controles_laterales" class="col-md-3">Activar controles laterales</label>
							<select id="activar_controles_laterales" style="border: none;border-color: transparent;border-bottom: 1px solid gray;" class="col-md-9" name="activar_controles_laterales"><option value="1" selected="selected">Si</option><option value="0">No</option></select>
						</div>
					</div>

				</div>

				<br><br>

				<table class="table table-bordered" id="ingreso_registros">
					<thead>
						<tr>
							<th> Imágen </th>
							<th> Texto informativo </th>
							<th> Enlace </th>
							<th> Acción </th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>

				<p>El peso máximo permitido para cada imágen es de <mark>2 MB</mark></p>
				<button type="button" style="background-color: transparent; color: #3394FF; border: none;" id="btn_nueva_linea"><i class="fa fa-btn fa-plus"></i> Agregar imágen </button>

				<br><br>


				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
				
			{{ Form::close() }}

		</div>
	</div>
	<br/><br/>


	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')

	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif

	<script type="text/javascript">
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

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
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
	</script>
@endsection