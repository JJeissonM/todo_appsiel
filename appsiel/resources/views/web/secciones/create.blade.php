@extends('layouts.principal')

<?php
	//use App\Http\Controllers\Sistema\VistaController;

	$tipos_elementos = [""=>"","Enlace"=>"Enlace","Imagen"=>"Imagen","Texto"=>"Texto","Módulo"=>"Módulo","Sección"=>"Sección"];
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
		    <h4>Crear nuevo registro</h4>
		    <hr>

		    @if( isset($url_action) )
		    	{{ Form::open(['url' => [$url_action], 'method' => 'POST','files' => true]) }}
			@else
				{{ Form::open(['url' => ['web'], 'method' => 'POST','files' => true]) }}
			@endif

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


				<!--
			-->

				<br><br>
				<?php

			        /*$datos = App\PaginaWeb\Carousel::get_array_datos( $registro->id );

			        //dd( $datos );

			        $imagenes = $datos['imagenes'];
  					$cant = count($imagenes);

  					$nombre_imagenes = json_decode($registro->imagenes);
  					*/
  					$cantidad_columnas = 1;//$registro->cantidad_columnas;
				?>
				<div id="contenedor_seccion" class="row">
					
					<div id="columna_1" style="border: dashed 2px red;" class="col-md-11 columna">
						Columna 1 <br>
						<button type="button" class="btn_nuevo_elemento" style="background-color: transparent; color: #3394FF; border: none;"><i class="fa fa-btn fa-plus"></i> Agregar elemento </button>
						<br><br><br><br>

						<div class="elementos_columna">
							
						</div>

					</div>

					<div id="columna_2" class="col-md-1 columna">
						<button type="button" id="btn_agregar_columna" class="close" title="Agregar columna">&plus;</button>
						<div id="controles_columna_2" style="display: none;">
							<button type="button" id="btn_remover_columna" class="close" title="Remover columna">&times;</button>
							Columna 2 <br>
							<button type="button" class="btn_nuevo_elemento" style="background-color: transparent; color: #3394FF; border: none;"><i class="fa fa-btn fa-plus"></i> Agregar elemento </button>
							<br><br><br><br>
						</div>

						<div class="elementos_columna">
							
						</div>

					</div>						
				</div>


				

				<br><br>


				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
				
			{{ Form::close() }}

		</div>
	</div>
	<br/><br/>


	<div id="myModal" class="modal fade" role="dialog">
	  <div class="modal-dialog modal-sm">

	    <!-- Modal content-->
	    <div class="modal-content">
	      
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	      </div>

	      <div class="modal-body">
	        {{ Form::Spin(64) }}

	        <div class="form_elemento">
				<label for="tipo_elemento" class="col-md-3">Tipo</label>
				{{ Form::select('tipo_elemento',$tipos_elementos,null,[ 'class'=>"form-control", 'id'=>"tipo_elemento"]) }}
				
				<div class="controles_form">

					<div id="elemento_tipo_enlace" style="border: solid 1px;">
						<div class="row">
							<label for="tipo_elemento" class="col-md-3">URL</label>
							{{ Form::text('url',null,[ 'class'=>"col-md-9 form-control", 'id'=>"url"]) }}
						</div>
					</div>


					
				</div>
			</div>
	      </div>

	      <div class="modal-footer">
	      	<button class="btn btn-danger btn-xs" data-dismiss="modal"> <i class="fa fa-close"></i> Cerrar </button>
	        
	        <button class="btn btn-success btn-xs btn_agregar_elemento"> <i class="fa fa-check"></i> Agregar </button>

	    		<br><br>
	      </div>

	    </div>
	  </div>
	</div>

	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')

	<script type="text/javascript">

		$(document).ready(function(){
			
			$("#btn_agregar_columna").on('click',function(){
				$("#columna_1").attr('class','col-md-6');
				$("#columna_2").attr('class','col-md-6');
				$("#columna_2").css('border','dashed 2px red');

				$(this).hide();
				$("#controles_columna_2").show();
			});
			
			$("#btn_remover_columna").on('click',function(){

				if ( !validar_elementos_columna2() ) {
					$("#columna_1").attr('class','col-md-11');
					$("#columna_2").attr('class','col-md-1');
					$("#columna_2").removeAttr('style');

					$("#controles_columna_2").hide();
					$("#btn_agregar_columna").show();
				}else{
					alert('La columna 2 tiene elementos. Debe retirarlos para poder eliminarla.');
				}
			});

			// Mostrar "formulario" para agregar nuevo elemento
			$(".btn_nuevo_elemento").on('click',function(e){
				e.preventDefault();

				columna_padre = $(this).parents(".columna");

				$("#myModal").modal({keyboard: "true"});
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

			function validar_elementos_columna2(){
				return false;
			}

		});
	</script>
@endsection