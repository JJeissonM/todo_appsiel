@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Ingreso de registros de nómina</h4>
		    <hr>
			{{ Form::open( array( 'url'=>'nomina/crear_registros2?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) ) }}

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('nom_doc_encabezado_id','','Seleccionar Nómina',$documentos,['required'=>'required']) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('nom_concepto_id','','Seleccionar concepto',$conceptos,['required'=>'required']) }}
				</div>
								
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-6">
						<br/>
						<button type="submit" class="btn btn-primary" id="btn_continuar">
							<i class="fa fa-btn fa-arrow-right"></i>Continuar
						</button>
						<br/><br/>
					</div>
				</div>
				
				<br/><br/>
			{{ Form::close() }}

		</div>
	</div>
	<br/><br/>	
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){

			$("#nom_doc_encabezado_id").focus();

			$("#nom_doc_encabezado_id").on('change',function(){
				$("#nom_concepto_id").focus();
			});
			
			$("#nom_concepto_id").on('change',function(){
				$("#btn_continuar").focus();
			});


		});
	</script>
@endsection