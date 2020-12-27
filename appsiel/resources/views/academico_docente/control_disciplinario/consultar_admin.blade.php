@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			
			&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( url('matriculas/control_disciplinario/precreate/0/0?id='.Input::get('id') ) ) }}

			{{ Form::open(['url'=>'matriculas/ajax_consultar_control_disciplinario2','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-2">
						&nbsp;
					</div>
					<div class="col-sm-2">
						&nbsp;
					</div>
					<div class="col-sm-3">
						{{ Form::label('semana_id','Semana') }}
						<br/>
						{{ Form::select('semana_id',$semanas,null, [ 'class' => 'combobox', 'id' => 'semana_id' ]) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('curso_id','Curso') }}
						<br/>
						{{ Form::select('curso_id',$cursos,null, [ 'class' => 'form-control', 'id' => 'curso_id' ]) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
					</div>
				</div>
				
			{{ Form::close() }}
			<!-- <button id="btn_ir">ir</button>	-->
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('control_disciplinario') }}
			{{ Form::bsBtnPdf('control_disciplinario') }}

			<br><br>

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			var id = getParameterByName('id');

			$('#semana_id').focus();

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos()){
					alert('Faltan campos por llenar.');
					return false;
				}

				$('#resultado_consulta').html( '' );
				$('#div_cargando').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#resultado_consulta').html(respuesta);
					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);

					var fecha = $('#semana_id').val().split('a3p0');

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) 
					{
						var new_url = url_pdf.replace('a3p0','matriculas/control_disciplinario/imprimir/' + $('#curso_id').val() + '/' + fecha[1] + '?id=' + id);
					}else{
						n = url_pdf.search('matriculas/control_disciplinario/imprimir');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'matriculas/control_disciplinario/imprimir/' + $('#curso_id').val() + '/' + fecha[1] + '?id=' + id;
					}		
					
					$('#btn_pdf').attr('href', new_url);
				});
			});

			function valida_campos(){
				var valida = true;
				if( $('#asignatura_id').val() == '' || $('#curso_id').val() == '' )
				{
					valida = false;
				}
				return valida;
			}

			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}
		});

		
	</script>
@endsection