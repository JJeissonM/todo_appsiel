@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="row">
				{{ Form::open(['url'=>'ajax_movimiento','id'=>'form_consulta']) }}
					<div class="col-sm-2">
						{{ Form::label('fecha_inicial','Fecha inicial') }}
						{{ Form::date('fecha_inicial',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_inicial']) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label('fecha_final','Fecha final') }}
						{{ Form::date('fecha_final',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_final']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('mov_bodega_id','Bodega') }}
						{{ Form::select('mov_bodega_id',$bodegas,null,['class'=>'form-control','id'=>'mov_bodega_id']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('grupo_inventario_id','Grupo') }}
						{{ Form::select('grupo_inventario_id',$grupo_inventario,null,['class'=>'form-control','id'=>'grupo_inventario_id']) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
					</div>
				{{ Form::close() }}
						<!-- <button id="btn_ir">ir</button> -->
			</div>
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<br/>
			<div class="row" id="spin" style="display: none;">
                <img src="{{asset('assets/img/spinning-wheel.gif')}}" width="32px" height="32px">
            </div>
			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('#fecha_corte').change(function(event){
				var id = getParameterByName('id');
				var fecha_corte = $('#fecha_corte').val();
				var bodega_id = $('#bodega_id').val();

				window.location.assign('../inventarios/'+bodega_id+'?id='+id+'&fecha_corte='+fecha_corte);

				//$('#consultar_existencias').attr('href','../inventarios/'+bodega_id+'?id='+id+'&fecha_corte='+fecha_corte);
			});

			/*function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}*/
		});

		
	</script>
@endsection