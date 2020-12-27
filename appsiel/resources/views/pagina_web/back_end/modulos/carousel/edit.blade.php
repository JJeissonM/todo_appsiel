@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;

	$modelo = App\Sistema\Modelo::find(Input::get('id_modelo'));
	$url_action = 'web/'.$registro->id;
    if ($modelo->url_form_create != '') {
        $url_action = $modelo->url_form_create.'/'.$registro->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
    }
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
		    <h4>Modificando el registro</h4>
		    <hr>

		    @if( isset($url_action) )
		    	{{ Form::model($registro, ['url' => [$url_action], 'method' => 'PUT','files' => true]) }}
			@else
				{{ Form::model($registro, ['url' => ['web/'.$registro->id], 'method' => 'PUT','files' => true]) }}
			@endif

				<?php
					$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';
				?>

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<label for="descripcion" class="col-md-3">*Descripción</label>
							{{ Form::text('descripcion',null,[ 'class'=>"col-md-9", 'id'=>"descripcion", 'style'=>"border: none;border-color: transparent;border-bottom: 1px solid gray;", 'required'=>"required"]) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<label for="altura_maxima" class="col-md-3">*Altura máxima</label>							
							{{ Form::text('altura_maxima',null,[ 'class'=>"col-md-9", 'id'=>"altura_maxima", 'style'=>"border: none;border-color: transparent;border-bottom: 1px solid gray;", 'required'=>"required"]) }}
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<label for="estado" class="col-md-3">Estado</label>
							{{ Form::select('estado',["Activo"=>"Activo","Inactivo"=>"Inactivo"],null,[ 'class'=>"col-md-9", 'id'=>"altura_maxima", 'style'=>"border: none;border-color: transparent;border-bottom: 1px solid gray;", 'required'=>"required"]) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<label for="activar_controles_laterales" class="col-md-3">Activar controles laterales</label>
							{{ Form::select('activar_controles_laterales',["1"=>"Si","0"=>"No"],null,[ 'class'=>"col-md-9", 'id'=>"altura_maxima", 'style'=>"border: none;border-color: transparent;border-bottom: 1px solid gray;", 'required'=>"required"]) }}
						</div>
					</div>
				</div>

				<br><br>

				<?php

			        $datos = App\PaginaWeb\Carousel::get_array_datos( $registro->id );

			        //dd( $datos );

			        $imagenes = $datos['imagenes'];
  					$cant = count($imagenes);

  					$nombre_imagenes = json_decode($registro->imagenes);

				?>
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
						@for($i=0;$i<$cant;$i++)
							<tr>
								<td> <input type="hidden" name="imagenes_anteriores[]" value="{{ $nombre_imagenes[$i]->imagen }}"> <img height="150px" src="{{ $imagenes[$i]['imagen'] }}" /> </td>
								<td> <input type="text" class="form-control" name="textos_imagenes[]" value="{{ $imagenes[$i]['texto'] }}"> </td>
								<td> <input type="text" class="form-control" name="enlaces_imagenes[]" value="{{ $imagenes[$i]['enlace'] }}"> </td>
								<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button> </td>
							</tr>
						@endfor
					</tbody>
				</table>

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
		      addImage(e);
		      $(this).hide();
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