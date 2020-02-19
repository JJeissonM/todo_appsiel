@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

    		<br><br>

    		<div class="row">

    			<div class="col-md-6">
					<h4 style="text-align: center; width: 100%;"> Generar archivo (exportar) </h4>
					<hr>
					Este proceso almacena en el archivo de configuración <b>registros_tablas_bd.php</b> todos los registros de las tablas seleccionadas.

					<br>
    				NOTA: Normalmente se ejecuta desde la BD local.
					<br>
					<br>

		    		<div class="row">
		    			<div class="col-md-12">

		    				{{ Form::open( ['url'=>'exportar_tablas_bd', 'id' => 'form_create', 'files' => true ] ) }}
		    					<h3>Tablas del sistema</h3>
		    					<hr>
		    					@foreach( $tablas_bd as $key => $value)
		    						&nbsp;&nbsp;&nbsp; {{ Form::checkbox( 'tablas_a_exportar[]', $value, true) }}&nbsp;<b>{{ $value }}</b>
		    						&nbsp;&nbsp;&nbsp;
		    					@endforeach

								{{ Form::hidden('url_id',Input::get('id')) }}
								{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
								{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}
								
							{{ Form::close() }}

		    			</div>    				
		    		</div>

		    		<br>
					<br>
		    		
		    		<div class="row">
		    			<div class="col-md-12" style="text-align: center; color: green;">
		    				<div id="mensajes1">  </div>
		    			</div>    				
		    		</div>		    		

		    		<div class="row">
		    			<div class="col-md-12" style="text-align: center;">
		    				<br>
		    				<button class="btn btn-info btn-sm" id="btn_generar"> <i class="fa fa-copy"></i> Generar </button>

		    			</div>    				
		    		</div>

    			</div>


    			<div class="col-md-6">
    				<h4 style="text-align: center; width: 100%;"> LLenar tablas BD (importar) </h4>
					<hr>
					Este proceso almacena en la BD la información contenida en el archivo de configuración <b>registros_tablas_bd.php</b>

					<br>
					Primero debe presionar <b>Ver tablas</b> para que pueda Actualizar los registros. 

					<br>
    				<b>NOTA #1:</b> El proceso, primero vacía (TRUNCATE) las tablas de la BD y luego carga los nuevos registros.

					<br>
    				<b>NOTA #2:</b> NO se hacen validaciones de datos.

					<br>
					<br>

		    		<div class="row">
		    			<div class="col-md-12" style="text-align: center; color: green;">
		    				<div id="mensajes2">  </div>
		    			</div>    				
		    		</div>		    		

		    		<div class="row">
		    			<div class="col-md-6" style="text-align: center;">
		    				<br>
		    				<button class="btn btn-primary btn-sm" id="btn_ver_tablas"> <i class="fa fa-eye"></i> Ver tablas </button>

		    			</div> 
		    			<div class="col-md-6" style="text-align: center;">
		    				<br>
		    				<button class="btn btn-success btn-sm" id="btn_actualizar_registros" disabled="disabled"> <i class="fa fa-upload"></i> Actualizar registros </button>

		    			</div>    				
		    		</div>
    			</div>
    			
    		</div>    		

			{{ Form::Spin('128') }}

	    </div>

	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('input:checkbox').attr('checked',true);

			$("#btn_generar").on('click',function(event){
		    	event.preventDefault();

		    	$('#mensajes1').html('');
		    	$('#mensajes2').html('');
		    	
		    	$("#div_spin").show();
		 		$("#div_cargando").show();
				//$('#btn_generar').attr('disabled','disabled');

				// Preparar datos de los controles para enviar formulario
				var form_create = $('#form_create');
				var url = form_create.attr('action');
				var datos = form_create.serialize();
				
				// Enviar formulario vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#div_spin').hide();

					$('#mensajes1').html(respuesta);

				});
		    });

			$("#btn_ver_tablas").on('click',function(event){
		    	event.preventDefault();
		    	
		    	$('#mensajes1').html('');
		    	$('#mensajes2').html('');

		    	$("#div_spin").show();
		 		$("#div_cargando").show();

				var url = "{{ url('visualizar_tablas_archivo') }}";
				
				// Enviar formulario vía POST
				$.get(url,function(respuesta){
					$('#div_cargando').hide();
					$('#div_spin').hide();

					$('#mensajes2').html(respuesta);
					$('#btn_actualizar_registros').removeAttr('disabled');

				});
		    });

			$("#btn_actualizar_registros").on('click',function(event){
		    	event.preventDefault();
		    	
		    	if ( confirm("Realmente quiere vaciar las tablas mostradas y cargar los nuevos registros?") )
			 	{
			    	$('#mensajes1').html('');

			    	$("#div_spin").show();
			 		$("#div_cargando").show();

					var url = "{{ url('insertar_registros_tablas_bd') }}";
					
					// Enviar formulario vía POST
					$.get(url,function(respuesta){
						$('#div_cargando').hide();
						$('#div_spin').hide();

						$('#mensajes2').html(respuesta);
						$('#btn_actualizar_registros').removeAttr('disabled');

					});
				}
		    });

		});

	</script>
@endsection