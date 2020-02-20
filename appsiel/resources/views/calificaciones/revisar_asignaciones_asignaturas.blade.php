@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
    	<div class="marco_formulario">

    		<br/>
				{{ Form::bsBtnExcel('asignaturas_por_cursos') }}
				<button class="btn btn-info btn-sm" id="btn_imprimir">Imprimir</button>
    		<br/><br/>

    		{!! $tabla !!}
    		
	    </div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script>


		$(document).ready(function(){


			var id = getParameterByName('id');
			var id_modelo = getParameterByName('id_modelo');

			$('#btn_excel').show();

			$("#btn_imprimir").on('click',function(event){
		    	event.preventDefault();

				window.print();
		    });

			$('#periodo_lectivo_id').on('change',function()
			{
				if ( $(this).val() == '') { return false; }

				$('#btn_actualizar').attr('href','../calificaciones/revisar_asignaciones?id='+id+'&id_modelo='+$('#id_modelo').val()+'&periodo_lectivo_id='+$('#periodo_lectivo_id').val());					
			});
		});
	</script>
@endsection