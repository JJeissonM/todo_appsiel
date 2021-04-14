@extends('layouts.create')

@section('seccion_adicional')
	{{ Form::Spin(48) }}
	<div class="container-fluid" id="div_tabla_empleados">
		
	</div>
@endsection

@section('script_adicional')

	<script type="text/javascript">
		$(document).ready(function(){

			$("#nom_doc_encabezado_id").on('change',function(event){
				if( $('#nom_doc_encabezado_id').val() == '' )
				{ 
					alert('Debe seleccionar un proyecto.');
					return false;
				}

				if( $('#nom_concepto_id').val() != '' )
				{
					llamar_empleados();
				}				
			});

			$("#nom_concepto_id").on('change',function(event){

				if( $('#nom_doc_encabezado_id').val() == '' )
				{ 
					$('#nom_doc_encabezado_id').focus();
					alert('Debe seleccionar un proyecto.');
					return false;
				}

				if( $('#nom_concepto_id').val() == '' )
				{
					alert('Debe seleccionar un concepto.');
					return false;
				}

				llamar_empleados();
			});

			function llamar_empleados()
			{
		 		$("#div_spin").show();
		 		$("#div_spin").show();
				$("#div_tabla_empleados").html('');

				var url = "{{url('nom_get_tabla_empleados_ingreso_registros')}}";

				$.get( url, { nom_doc_encabezado_id: $('#nom_doc_encabezado_id').val(), nom_concepto_id: $('#nom_concepto_id').val() } )
				  .done(function( data ) {
			 		$("#div_spin").hide();
			 		$("#div_spin").hide();
				    $("#div_tabla_empleados").html(data);
				});
		    }

		});
	</script>

@endsection


