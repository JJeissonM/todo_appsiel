@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>

		    <?php
				
				$vendedor = App\Ventas\Vendedor::where( 'user_id', Auth::user()->id )->get()->first();
				$mensaje = '';

		        if ( !is_null( $vendedor) )
		        {
		            // El vendedor debe estar creado como cliente.
					// Se traen los mismos datos de cliente del vendedor para asignarselos a sus propios clientes
					$datos_cliente = App\Ventas\Cliente::find( $vendedor->cliente_id );
					if ( is_null( $datos_cliente) )
		        	{
		        		$mensaje = 'El vendedor NO está creado como cliente. Consulte con el administrador.';
		        	}else{
		        		$datos_tercero = App\Core\Tercero::find( $datos_cliente->core_tercero_id );
		        	}
		        }else{
		        	$mensaje = 'El usuario no está asociado a un vendedor. Consulte con el administrador.';
		        }

		        $url = htmlspecialchars($_SERVER['HTTP_REFERER']);

			?>

			@if( $mensaje != '')
				<h3>
					{{ $mensaje }}
				</h3>
			@else
				{{ Form::open(['url'=>$form_create['url'],'id'=>'form_create','files' => true]) }}

					<div class="row botones" style="margin: 5px;"> {{ Form::bsButtonsForm($url) }} </div>

					<div class="row" style="padding:5px;">
						<div class="form-group">
							<label class="control-label col-sm-3" for="numero_identificacion">*Cédula / NIT:</label>
							<div class="col-sm-9">
								<input class="form-control" id="numero_identificacion" autocomplete="off" required="required" name="numero_identificacion" type="text" value="{{ random_int(100, 999999) }}">
							</div>
						</div>
					</div>

					<div class="row" style="padding:5px;">
						<div class="form-group">
							<label class="control-label col-sm-3" for="descripcion">*Nombre establecimiento:</label>
							<div class="col-sm-9">
								<input class="form-control" id="descripcion" autocomplete="off" required="required" name="descripcion" type="text">
							</div>
						</div>
					</div>

					<div class="row" style="padding:5px;">
						<div class="form-group">
							<label class="control-label col-sm-3" for="razon_social">Razón social:</label>
							<div class="col-sm-9">
								<input class="form-control" id="razon_social" autocomplete="off" name="razon_social" type="text">
							</div>
						</div>
					</div>

					<div class="row" style="padding:5px;">
						<div class="form-group">
							<label class="control-label col-sm-3" for="direccion1">*Dirección:</label>
							<div class="col-sm-9">
								<input class="form-control" id="direccion1" autocomplete="off" required="required" name="direccion1" type="text">
							</div>
						</div>
					</div>

					<div class="row" style="padding:5px;">
						<div class="form-group">
							<label class="control-label col-sm-3" for="telefono1">*Teléfono:</label>
							<div class="col-sm-9">
								<input class="form-control" id="telefono1" autocomplete="off" required="required" name="telefono1" type="text">
							</div>
						</div>
					</div>

					
					{{ Form::hidden( 'codigo_ciudad', $datos_tercero->codigo_ciudad ) }}
					{{ Form::hidden( 'core_empresa_id', $datos_tercero->core_empresa_id ) }}
					{{ Form::hidden( 'tipo', $datos_tercero->tipo ) }}
					{{ Form::hidden( 'id_tipo_documento_id', $datos_tercero->id_tipo_documento_id ) }}
					{{ Form::hidden( 'digito_verificacion', $datos_tercero->digito_verificacion ) }}

					{{ Form::hidden( 'encabezado_dcto_pp_id', $datos_cliente->encabezado_dcto_pp_id ) }}
					{{ Form::hidden( 'clase_cliente_id', $datos_cliente->clase_cliente_id ) }}
					{{ Form::hidden( 'lista_precios_id', $datos_cliente->lista_precios_id ) }}
					{{ Form::hidden( 'lista_descuentos_id', $datos_cliente->lista_descuentos_id ) }}
					{{ Form::hidden( 'vendedor_id', $vendedor->id ) }}
					{{ Form::hidden( 'inv_bodega_id', $datos_cliente->inv_bodega_id ) }}
					{{ Form::hidden( 'zona_id', $datos_cliente->zona_id ) }}
					{{ Form::hidden( 'liquida_impuestos', $datos_cliente->liquida_impuestos ) }}
					{{ Form::hidden( 'condicion_pago_id', $datos_cliente->condicion_pago_id ) }}
					{{ Form::hidden( 'cupo_credito', $datos_cliente->cupo_credito ) }}
					{{ Form::hidden( 'bloquea_por_cupo', $datos_cliente->bloquea_por_cupo ) }}
					{{ Form::hidden( 'bloquea_por_mora', $datos_cliente->bloquea_por_mora ) }}
					
					{{ Form::hidden( 'estado', "Activo" ) }}
					{{ Form::hidden( 'creado_por', Auth::user()->email ) }}


					{{ Form::hidden('url_id',Input::get('id')) }}
					{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
					{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}
					
				{{ Form::close() }}
			@endif

				

		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){

			$('#fecha').val( get_fecha_hoy() );

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});

		});
	</script>
	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection