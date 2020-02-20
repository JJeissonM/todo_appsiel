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
		#contenedor_seccion{
			width: 100%;
			border: solid 2px gray;
		}
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

		    {{ Form::model($registro, ['url' => [$url_action], 'method' => 'PUT','files' => true]) }}

				<?php
					$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';
				?>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label col-sm-3" for="descripcion">*Descripción:</label>
							<div class="col-sm-9">
								{{ Form::text('descripcion',null, array_merge(['class' => 'form-control','id'=>'descripcion','placeholder'=>'*Descripción', 'required'=>"required"], [])) }}
							</div>
						</div>
					</div>

					<div class="col-md-6">

						<div class="form-group" style="padding-left: 5px;">
							<label class="control-label col-sm-2" for="detalle" style="padding-left: 5px;"> *Detalle: </label>
							<div class="col-sm-10">
								{{ Form::textarea('detalle',null, array_merge(['class' => 'form-control', 'rows' => '2', 'required'=>"required"], [])) }}
							</div>
						</div>
					</div>

				</div>


				<div class="row">
					<div class="col-md-6">

						<div class="form-group">
							<label class="control-label col-sm-3" for="mostrar_titulo">*Mostrar título:</label>
							<div class="col-sm-9">
								{{ Form::select('mostrar_titulo',["0"=>"No","1"=>"Si"],null, array_merge( [ 'class'=>"form-control", 'id' => 'mostrar_titulo', 'required'=>"required" ], [] )) }}
							</div>
						</div>
					</div>

				</div>

				<br><br>

				<?php

			        /*$datos = App\PaginaWeb\Carousel::get_array_datos( $registro->id );

			        //dd( $datos );

			        $imagenes = $datos['imagenes'];
  					$cant = count($imagenes);

  					$nombre_imagenes = json_decode($registro->imagenes);
  					*/
  					$cantidad_columnas = $registro->cantidad_columnas;
				?>
				<div id="contenedor_seccion" class="row">
					@for($i=1;$i<=$cantidad_columnas;$i++)
						<div id="columna_{{$i}}" style="border: dashed 2px red;" class="col-md-{{ 12/$cantidad_columnas }} columna">

							<button type="button" class="btn_nuevo_elemento" style="background-color: transparent; color: #3394FF; border: none;"><i class="fa fa-btn fa-plus"></i> Agregar elemento </button>

							<br><br>

							<div class="form_elemento" style="display: none; border: solid 1px gray; padding: 15px; height: 100%;">
								<label for="tipo_elemento" class="col-md-3">Tipo</label>
								{{ Form::select('tipo_elemento',[""=>"","Enlace"=>"Enlace","Imagen"=>"Imagen","Texto"=>"Texto"],null,[ 'class'=>"col-md-9", 'id'=>"tipo_elemento", 'style'=>"border: none;border-color: transparent;border-bottom: 1px solid gray;", 'required'=>"required"]) }}
								<div class="controles_form">
									
								</div>
							</div>

							<br><br>
							<a href="{{ url('/') }}" class="fa fa-facebook" target="_blank"></a>
							elementos columna {{$i}}
						</div>
					@endfor
				</div>


				

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

	<script type="text/javascript">
		
		var cantidad_columnas = {{$registro->cantidad_columnas}};
		var columna_padre;
		
		//

		$(document).ready(function(){

			
			$("#cantidad_columnas").on('change',function(e){
				cantidad_columnas = $(this).val();

				console.log( $(this).val() );
			});

			// Mostrar "formulario" para agregar nuevo elemento
			$(".btn_nuevo_elemento").on('click',function(e){
				e.preventDefault();

				columna_padre = $(this).parents(".columna");
				console.log( columna_padre );
				columna_padre.find(".form_elemento").show();
			});

			$(".tipo_elemento").on('change',function(e){

				columna_padre = $(this).parents(".columna");

				switch( $(this).val() ){
					case 'Enlace':
						columna_padre.find(".controles_form").append('<input type="text" class="form-control" name="link[]"');
					break;
					default:
					break;
				}
			});

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