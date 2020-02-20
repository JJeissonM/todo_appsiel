@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	@include('layouts.mensajes')
	<div class="row">
		<div class="col col-sm-10 col-sm-offset-1">
		<div class="row">
			<div class="col col-sm-10 col-sm-offset-1">
				<div class="panel panel-default">

					<div class="panel-body">
						<table class="table table-striped">
							{{ Form::bsTableHeader(['Nombre','Empresa','Correo','Acción']) }}
							<tbody>
									<tr>
										<td>
											{{ $usuario->name }}
										</td>
										<td>
											{{ $empresa->descripcion }}
										</td>
										<td>
											{{ $usuario->email }}
										</td>
										<td>
											@can('Cambiar Empresa')
											<button id="btn_cambiar_empresa" class="btn btn-info btn-sm"><i class="fa fa-btn fa-building"></i> Cambiar empresa</button>
											@endcan
											<?php
											$url = 'core/usuario/perfil/cambiar_mi_passwd?id='.Input::get('id').'&ruta=/core/usuario/perfil?id='.Input::get('id');
											?>
											<a id="btn_cambiar_password" name="btn_add" class="btn btn-warning btn-sm" href="{{ url($url) }}"><i class="fa fa-btn fa-key"></i> Cambiar contraseña</a>
										</td>
									</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $url_store='/mi_url'; ?>

<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="titulo_modal">Cambiar empresa</h4>
      </div>
      <div class="modal-body">
        {{ Form::open(['url'=>$url_store,'id'=>'mi_formulario']) }}
        	<div class="row" id="spin" style="display: none;">
                <img src="{{asset('assets/img/spinning-wheel.gif')}}" width="32px" height="32px">
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsSelect('empresa_id',null,'Empresa',[''=>''],[]) }}
            </div>

            {{ Form::hidden( 'user_id', $usuario->id ) }}
            {{ Form::hidden( 'id' , Input::get( 'id' ) ) }}

        {{ Form::close() }}
      </div>
      <div class="modal-footer">
        <button id="btn_guardar" type="button" class="btn btn-success">Guardar</button>
      </div>
    </div>

  </div>
</div> 
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){
			/*
			**	Abrir formulario de productos
			*/
			$("#btn_cambiar_empresa").click(function(){
		        $("#myModal").modal({backdrop: "static"});
		    });
		    	
		    	// Al mostrar la ventana modal
		    $("#myModal,#myModal2").on('shown.bs.modal', function () {
		    	$('#spin').show();		
				var url = 'perfil/cambiar_empresa';
				$.get( url, function( datos ) {
			        $('#spin').hide();
			        //console.log(datos);
					$("#empresa_id").html(datos[0]);
					var formAction = $("#mi_formulario").attr('action');

		    	//console.log('formAction: '+formAction);
					var mi_url = formAction.replace("mi_url", datos[1]);
		    	//console.log('mi_url nueva: '+mi_url);
					$("#mi_formulario").attr('action',mi_url);
				});
		    });

		    $("#btn_guardar").click(function(){
		    	console.log($("#mi_formulario").attr('action'));
		        $('#mi_formulario').submit();
		    });
		});
	</script>
@endsection