@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
	<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
	    <h4 style="color: gray;">Revisión de boletines</h4>
	    <hr>

	    {{Form::open(array('route'=>array('revision'),'method'=>'POST','class'=>'form-horizontal','id'=>'form-revisar'))}}

			<div class="row" style="padding:5px;">
				{{ Form::bsSelect('id_periodo','','Seleccionar periodo',$periodos,['required'=>'required']) }}
			</div>

			<div class="row" style="padding:5px;">
				{{ Form::bsSelect('curso_id','','Seleccionar curso',$cursos,['required'=>'required']) }}
			</div>
								
			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-6">
					<br/>
					<button class="btn btn-primary" id="btn_revisar">
						<i class="fa fa-btn fa-check"></i> Revisar
					</button>
					<br/><br/>
				</div>
			</div> 
		{{Form::close()}}
		
		</div>
	</div>

	<br/><br/>
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
			<div align="center" >

					{{ Form::Spin(64) }}
					<div id="div_resultado">
						
					</div>
			</div>
		</div>
	</div>

	<br/><br/><br/><br/>
	
@endsection


@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			var obj_textarea;

			$('#btn_revisar').click(function(e){
				e.preventDefault();


				$("#div_resultado").html('');

				if ( !validar_requeridos() ) { return false; }
				$('#div_spin').show();
				var form = $("#form-revisar");
				var url = form.attr( "action" );
				var datos = form.serialize();

				$.post(url, datos, function( respuesta ){
					$('#div_spin').hide();
					$("#div_resultado").html(respuesta);
				});

		    });

		    $(document).on('dblclick','textarea',function(){
				$(this).removeAttr( "disabled" );
				$(this).next().show(); // Mostrar el botón
				$(this).focus();
				//console.log();
		    });

		    var boton;
		    $(document).on('click','.btn_guardar_observacion',function(){
				var textarea = $(this).prev();
				boton = $(this);
				$("#codigo_matricula").val( $(this).attr('data-codigo_matricula') );
				$("#id_colegio").val( $(this).attr('data-id_colegio') );
				$("#id_periodo").val( $(this).attr('data-id_periodo') );
				$("#curso_id").val( $(this).attr('data-curso_id') );
				$("#id_estudiante").val( $(this).attr('data-id_estudiante') );
				$("#observacion").val( textarea.val() );
				$("#observacion_id").val( $(this).attr('data-observacion_id') );


				var form = $("#form_auxiliar");
				var url = form.attr( "action" );
				var datos = form.serialize();

				$.post(url, datos, function( respuesta ){
					boton.attr( 'data-observacion_id', respuesta[0] );
				});

				$(this).hide(); // Ocutar el botón
				textarea.attr('disabled','disabled');
		    });




		});
	</script>
@endsection