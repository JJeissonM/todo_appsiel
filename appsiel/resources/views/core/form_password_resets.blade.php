@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
    	<div class="marco_formulario">

    		<br><br>

    		<div class="well">
				<h4 style="text-align: center; width: 100%;"> Resetear contraseñas </h4>
				Este proceso crea nuevas contraseñas para TODOS los usuarios del perfil seleccionado.
				
    			<br><br><br>

    			<div class="alert alert-warning">
				  <strong>¡Sea cuidadoso al ejecutar este proceso!</strong> No se podrá revertir.
				</div>
			</div>

    		<div class="row">
    			<div class="col-md-5">

    				<div class="row" style="padding:5px;">					
						<label class="control-label col-sm-4" > <b> Perfil: </b> </label>

						<div class="col-sm-8">
							{{ Form::select('role_id', $roles, null, ['id' => 'role_id', 'class' => 'form-control' ] ) }}
						</div>					 
					</div>

    			</div>

    			<div class="col-md-5">

    				<div class="row" style="padding:5px;">					
						{{ Form::checkbox( 'confirmar', null, false,['disabled'=>'disabled', 'id'=>'confirmar']) }} &nbsp;<b> Confirmar </b> 
					</div>

    			</div>

    			<div class="col-md-2">
    				<button class="btn btn-info btn-sm" id="btn_reset_passwords" disabled="disabled"> <i class="fa fa-copy"></i> Cambiar contraseñas </button>
    			</div>    				
    		</div>
    		

			{{ Form::Spin('128') }}

			<div class="row">
				<div class="alert alert-success" id="mensaje_ok" style="display: none;">
				  <strong>¡TODAS las contraseñas del perfil fueron cambiadas correctamente!</strong>
				  <br>
				  Puede consultar las contraseñas cambiadas <a href="{{ url('web?id='.Input::get('id').'&id_modelo=194') }}">AQUÍ</a>
				</div>	
			</div>

	    </div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('#role_id').focus();

			$('#role_id').on('change',function()
			{
				$('#popup_alerta_danger').hide();
				$('#btn_reset_passwords').attr('disabled','disabled');

				if ( $(this).val() == '')
				{ 
					mostrar_popup( 'Debe seleccionar un perfil.' );
					$(this).focus();
					$('#confirmar').removeAttr('checked');
	        		$('#confirmar').attr('disabled','disabled');
					return false;
				}

				$('#popup_alerta_danger').hide();
				$('#confirmar').removeAttr('disabled');
				$('#confirmar').focus();

			});

			$('#confirmar').on('change',function()
			{
				if ( !$(this).is(":checked") )
	        	{
	        		$('#btn_reset_passwords').attr('disabled','disabled');
	        		return false;
	        	}else{
	        		$('#btn_reset_passwords').removeAttr('disabled');
	        	}
			});				

			function mostrar_popup( mensaje )
			{
				$('#popup_alerta_danger').show();
				$('#popup_alerta_danger').text( mensaje );
			}

			$("#btn_reset_passwords").on('click',function(event){
		    	event.preventDefault();

		    	
		    	if ( confirm("Realmente quiere CAMBIAR las contraseñas de TODOS LOS USUARIOS para el perfil " + $('#role_id option:selected' ).text() + "?") )
			 	{
			 		$("#div_spin").show();
			 		$("#div_cargando").show();
					$('#btn_reset_passwords').attr('disabled','disabled');

			 		var role_id = $('#role_id').val();

					var url = "{{ url('config_password_resets') }}" + "/" + role_id;

					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){

			        		$('#div_cargando').hide();
			        		$("#div_spin").hide();

			        		$("#mensaje_ok").show();
				        }
				    });

			 		
			 	}
		    });

		});

	</script>
@endsection