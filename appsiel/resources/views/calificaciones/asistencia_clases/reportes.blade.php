@extends('layouts.principal')

@section('content')
	
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="row">
				<div class="col-sm-2">
					{{ Form::label('fecha_inicial','Fecha inicial') }}
					{{ Form::date('fecha_inicial',date('Y-m-d') ,['class'=>'form-control','id'=>'fecha_inicial']) }}
				</div>
				<div class="col-sm-2">
					{{ Form::label('fecha_final','Fecha final') }}
					{{ Form::date('fecha_final',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_final']) }}
				</div>
				<div class="col-sm-2">
					{{ Form::label('curso_id','Curso') }}
						<br/>
					{{ Form::select('curso_id',$registros,null, [ 'class' => 'form-control', 'id' => 'curso_id', 'required' => 'required' ]) }}

				</div>
				<div class="col-sm-3">
					{{ Form::label('tipo_reporte','Tipo reporte') }}
					{{ Form::select('tipo_reporte',[
							'planilla_asistencias'=>'Planilla de Asistencia',
							'cantidad_inasistencias'=>'Fallas por estudiante'
						],null,['class'=>'form-control','id'=>'tipo_reporte']) }}
				</div>
				<div class="col-sm-3">
					{{ Form::label(' ','.') }}
					<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
				</div>
			</div>
		</div>
	</div>
	<hr>
	
	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('reporte_asistencia') }}
			{{ Form::bsBtnPdf('reporte_asistencia') }}

			{{ Form::Spin(128) }}

			<div id="div_resultado">

			</div>	
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script>
		$(document).ready(function(){
			$('#btn_generar').click(function(){

				if ( !validar_requeridos() )
				{
					return false;
				}

				$("#div_resultado").html('');
				$("#div_spin").show();

				var fecha_inicial = $("#fecha_inicial").val();
				var fecha_final = $("#fecha_final").val();
				
				var curso_id = 0;
				if ($("#curso_id").val() != '') {
					var curso_id = $("#curso_id").val();
				}
				
				var tipo_reporte = $("#tipo_reporte").val();
				
				var url = '../asistencia_clases/generar_reporte/'+fecha_inicial+'/'+fecha_final+'/'+curso_id+'/'+tipo_reporte;
				$.get(url,function(result){
					$("#div_spin").hide();
					$("#btn_excel").show();
					$("#div_resultado").html(result);
				});
			});
		});
	</script>
@endsection