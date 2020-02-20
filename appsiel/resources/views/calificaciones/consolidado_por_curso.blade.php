
@extends('layouts.principal')




<!-- YA NO SE ESTA USANDO, SE USA EL ReporteController -->



@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			
			{{ Form::open(['url'=>'calificaciones/ajax_reporte_consolidado_por_curso','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-2">
						&nbsp;
					</div>
					<div class="col-sm-2">
						&nbsp;
					</div>
					<div class="col-sm-3">
						{{ Form::label('periodo_id','Periodo') }}
						<br/>
						{{ Form::select('periodo_id',$periodos,null, [ 'class' => 'form-control', 'id' => 'periodo_id', 'required' => 'required' ]) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('curso_id','Curso') }}
						<br/>
						{{ Form::select('curso_id',$cursos,null, [ 'class' => 'form-control', 'id' => 'curso_id', 'required' => 'required' ]) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
					</div>
				</div>
				
			{{ Form::close() }}
			<!--<button id="btn_ir">ir</button>		-->
			
		</div>
	</div>

	<hr>

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('reporte_consolidado_por_curso') }}
			{{ Form::bsBtnPdf('reporte_consolidado_por_curso') }}

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#periodo_id').focus();

			$('#periodo_id').change(function(){
				if ($('#periodo_id').val()!='')
				{
					$('#curso_id').focus();
				}
			});

			$('#curso_id').change(function(){
				if ($('#curso_id').val()!='')
				{
					$('#btn_generar').focus();
				}
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event)
			{
				if(!valida_campos()){
					alert('Debe seleccionar todos los campos.');
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

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace('a3p0','cali_pdf_reporte_consolidado_por_curso?'+datos);
					}else{
						n = url_pdf.search('cali_pdf_reporte_consolidado_por_curso');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'cali_pdf_reporte_consolidado_por_curso?' + datos;
					}
					
					
					$('#btn_pdf').attr('href', new_url);
				});
			});

			function valida_campos(){
				var valida = true;
				if( $('#periodo_id').val() == '' || $('#curso_id').val() == '' )
				{
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection