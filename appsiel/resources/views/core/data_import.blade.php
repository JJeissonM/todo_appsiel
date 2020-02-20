@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
	<div class="marco_formulario">
	    <h4>Importar datos</h4>
	    <hr>

		{{ Form::open(['url' => 'importar/formulario','files' => true]) }}
			<?php			  
			  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
			  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';

			  	$empresa_id = Auth::user()->empresa_id;
                $empresa = App\Core\Empresa::find($empresa_id);
                $valor = $empresa->descripcion;

			?>

			<div class="alert alert-info">
			  <strong>Parámetros de selección</strong>
			  <br/><br/>
				  {{ Form::bsLabel('core_empresa_id',[$valor,$empresa_id],'Empresa', []) }}
				<br/><br/>
				{{ Form::bsSelect('modelo_id', null, 'Modelo', $modelos, ['required' => 'required']) }}
				<br/><br/>
				<div class="row" id="lbl_cuentas_terceros" style="display: none; border: dashed 1px gray;">
					<div class="col-md-4">
						{{ Form::bsSelect('contab_anticipo_cta_id', null, 'Cta. ANTICIPOS por defecto', $cuentas, ['class' => 'combobox cuentas']) }}
					</div>
					<div class="col-md-4">
						{{ Form::bsSelect('contab_cartera_cta_id', null, 'Cta. CARTERA por defecto', $cuentas, ['class' => 'combobox cuentas']) }}
					</div>
					<div class="col-md-4">
						{{ Form::bsSelect('contab_cxp_cta_id', null, 'Cta. X PAGAR por defecto', $cuentas, ['class' => 'combobox cuentas']) }}
					</div>
				</div>
				<br/><br/>
				<div class="row" id="lbl_datos_inmuebles" style="display: none; border: dashed 1px gray;">
					<div class="col-md-12">
						{{ Form::bsSelect('cxc_servicio_id', null, 'Cpto. a Facturar por defecto', $cxc_servicios, ['class' => 'datos_inmuebles']) }}
					</div>
				</div>
				<br/><br/>
				{{ Form::file('archivo', ['class' => 'form-control', 'required' => 'required' ]) }}


				<br/><br/>
				<span style="color: red; font-weight: bold;">Nota: El archivo de excel no debe tener celdas vacías.</span>
				
				<br/><br/>
			</div>

			

			{{ Form::hidden('url_id',Input::get('id')) }}
			{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
			
		{{ Form::close() }}

		<div class="alert alert-success">
		  <strong>¡Orden para importar datos!</strong>
		  <br/><br/>
		  <div class="list-group">
			  <a href="#" class="list-group-item">1) Grupo de cuentas</a>
			  <a href="#" class="list-group-item">2) Cuentas</a>
			  <a href="#" class="list-group-item">3) Terceros (clientes)</a>
			  <a href="#" class="list-group-item">4) Inmuebles</a>
			  <a href="#" class="list-group-item">3) Terceros (proveedores)</a>
			  <a href="#" class="list-group-item">5) Movimiento Contable</a>
			</div>
		</div>

	</div>
</div>
<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('.btn-primary').hide();

			$("#modelo_id").change( function(){
				switch( $("#modelo_id").val() ){
					case '48': // Grupo de cuentas
						$('.btn-primary').show(500);
						
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);
						break;

					case '49': // Cuentas
						$('.btn-primary').show(500);

						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);					
						break;

					case '7': // Terceros
						$('#lbl_cuentas_terceros').show(500);
						$('.cuentas').attr('required','required');

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '39': // Inmuebles
						$('#lbl_datos_inmuebles').show(500);
						$('.datos_inmuebles').attr('required','required');

						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.btn-primary').show(500);
						break;

					case '70': // Movimiento contable
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '66': // Inscripciones
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '29': // Estudiantes
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '19': // Matrículas
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '21': // Bodegas
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '22': // Items
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '25': // Documentos de inventario
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '72': // Movimientos inventarios
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '95': // Pacientes
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '96': // Consultas Médicas
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					case '138': // Clientes
						$('.cuentas').removeAttr('required');
						$('#lbl_cuentas_terceros').hide(500);

						$('.datos_inmuebles').removeAttr('required');
						$('#lbl_datos_inmuebles').hide(500);

						$('.btn-primary').show(500);
						break;

					default:
						$('#lbl_datos_inmuebles').hide(500);
						$('#lbl_cuentas_terceros').hide(500);
						$('.btn-primary').hide(500);
						break;
				}

			});
			
		});
	</script>
@endsection