@extends('layouts.reportes')

@section('sidebar')
	{{ Form::open(['url'=>'ajax_existencias','id'=>'form_consulta']) }}
		{{ Form::label('fecha_corte','Fecha corte') }}
		{{ Form::date('fecha_corte',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_corte','disabled'=>'disabled']) }}

		{{ Form::label('mov_bodega_id','Bodega') }}
		{{ Form::select('mov_bodega_id',$bodegas,1,['class'=>'form-control','id'=>'mov_bodega_id','disabled'=>'disabled']) }}

		<!-- {{ Form::label(' ','.') }}
		<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
	-->
	{{ Form::close() }}
@endsection


@section('contenido')
		<div class="col-md-12 marco_formulario">
			<br/>
			<div class="row" id="spin" style="display: none;">
                <img src="{{asset('assets/img/spinning-wheel.gif')}}" width="32px" height="32px">
            </div>
			<div id="resultado_consulta">
				<div style="font-weight: bold; font-size: 15px;">
					<br><br>
					Bodega <span id="lbl_bodega"></span>
					<br>
					Fecha: <span id="lbl_fecha"></span>
				</div>
				{!! $tabla !!}
			</div>	
		</div>
@endsection

@section('scripts_reporte')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#fecha_corte').focus();
			$('#btn_excel').show();
			$('#lbl_bodega').html( $('#mov_bodega_id option:selected').text() );
			$('#lbl_fecha').html( $('#fecha_corte').val() );
		});		
	</script>
@endsection