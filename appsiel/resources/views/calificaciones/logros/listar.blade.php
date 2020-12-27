@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Generar listados de logros</h4>
		    <hr>

		    {{Form::open(array('route'=>array('calificaciones.logros.update','listado'),'method'=>'PUT')) }}

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('id_nivel', null, 'Nivel académico', $niveles, ['id'=>'nivel']) }}
				</div>

				<div class="row" style="padding:5px;">
					<div id="spin" style="display: none;">
						<img src="{{asset('assets/img/spinning-wheel.gif')}}" width="32px" height="32px">
					</div>
				</div>

				<div class="row" style="padding:5px;">
					{{Form::bsSelect('id_asignatura', null, 'Asignatura',[], [])}}
				</div>

				<div class="row" style="padding:5px;">
					{{Form::bsSelect('tam_hoja', null, 'Tamaño hoja',['letter'=>'Carta','legal'=>'Oficio'], [])}}
				</div>

				<div class="row" style="padding:5px;">
					{{Form::bsSelect('orientacion', null, 'Orientación',['portrait'=>'Vertical','landscape'=>'Horizontal'], [])}}
				</div>
				
				<div class="form-group">
					<button type="submit" class="btn btn-primary">
						<i class="fa fa-btn fa-list"></i> Generar listado
					</button>
				</div>
			{{Form::close()}}


			{{Form::open(array('route'=>array('calificaciones.logros.update','buscar'),'method'=>'PUT','id'=>'form-buscar'))}}
				{!! Form::hidden('id_nivel','Ada', array('id' => 'id_nivel')) !!}

			{!! Form::close() !!}
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script>
		$(document).ready(function(){
			$("#nivel").on('change',function(){
				//alert("cambio");
                $('#spin').show();
				var nivel = $(this).val();
				var form = $('#form-buscar');
				var url = form.attr('action');
				$("#id_nivel").val(nivel);
				data = form.serialize();
				//alert(data);
				$.post(url,data,function(datos){
                    $('#spin').hide();
					$("#id_asignatura").html(datos);
				});
			});
			
			
			$("#tam_hoja").on('change',function(){
				var tam = $(this).val();
				if(tam=="Letter"){
					$("#cantidad_lineas").val(36);
				}else{
					$("#cantidad_lineas").val(47);
				}
			});
		});
	</script>
@endsection