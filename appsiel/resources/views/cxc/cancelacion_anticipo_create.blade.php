@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>
			{{ Form::open(['url'=>'web','id'=>'form_create']) }}

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
				{{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}

				{{ Form::hidden( 'core_tercero_id_aux', '', [ 'id' => 'core_tercero_id_aux' ] ) }}
				{{ Form::hidden( 'fecha_aux', '', [ 'id' => 'fecha_aux' ] ) }}
				{{ Form::hidden( 'tipo_recaudo_aux', '', [ 'id' => 'tipo_recaudo_aux' ] ) }}

			{{ Form::close() }}
			<button type="button" class="btn btn-primary btn-xs" id="btn_continuar1"><i class="fa fa-btn fa-forward"></i> Continuar</button>

            <br/><br/>
            <div class="row">
            	<div class="col-md-12">
            		<div id="div_documentos_cartera" style="display: none;">
		        		<h4>Documentos pendientes de cartera</h4>
						<hr>
		            	<div id="div_cartera">

		            	</div>
		            </div>
            	</div>
            </div>

            <br/>

            <div class="row">
            	<div class="col-md-12">
            		<div id="div_documentos_a_cancelar" style="display: none;">
					    <h4>Documentos seleccionados</h4>
					    <hr>
			            <table class="table table-bordered" id="documentos_a_cancelar">
			            	<thead>
						        <tr>
						            <th data-override="cxc_movimiento_id"></th>
						            <th>Inmueble</th>
						            <th>Documento</th>
						            <th>Fecha</th>
						            <th>Fecha Vence</th>
						            <th data-override="valor_cartera">Vlr. Cartera</th>
						            <th data-override="saldo_pendiente">Saldo pend.</th>
						            <th data-override="valor_aplicar">Vlr. aplicar</th>
						            <th>&nbsp;</th>
						        </tr>
						    </thead>
						    <tbody>
						    </tbody>
					        <tfoot>
					            <tr>
					                <td colspan="7">&nbsp;</td>
					                <td> <div id="total_valor"></div> </td>
					                <td> &nbsp;</td>
					            </tr>
					            <tr>
					                <td colspan="6">&nbsp;</td>
					                <td> <div id="lbl_total_pendiente" style="color: red;"></div></td>
					                <td> <div id="total_pendiente" style="color: red;"></div> </td>
					                <td> &nbsp;</td>
					            </tr>
					        </tfoot>
						</table>						
					</div>
            	</div>
            </div>

            <div class="row">
            	<div class="col-md-12">
            		
            	</div>            	
            </div>

            <div class="row">
            	<div class="col-md-2">
            	</div>
            	<div class="col-md-4">
            		<button type="button" class="btn btn-danger" id="btn_cancelar1" style="display: none;"><i class="fa fa-btn fa-remove"></i> Cancelar</button>
            	</div>
            	<div class="col-md-4">
            		<button type="button" class="btn btn-success" id="btn_guardar" style="display: none;"><i class="fa fa-btn fa-save"></i> Guardar </button>
            		<button type="button" class="btn btn-success" id="btn_guardar2" style="display: none;"><i class="fa fa-btn fa-save"></i> Guardar </button>
            	</div>
            	<div class="col-md-2">
            	</div>
            </div>			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	
	<script type="text/javascript">
		$(document).ready(function(){
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; //January is 0!
			var yyyy = today.getFullYear();

			if(dd<10) {
			    dd = '0'+dd
			} 

			if(mm<10) {
			    mm = '0'+mm
			} 

			today = yyyy + '-' + mm + '-' + dd;

			$('#fecha').val( today );
			$('#fecha').focus();
			
			$('#core_tercero_id_no').parent().hide();
			// Par que deje pasar el primer Continuar
			$('#core_tercero_id_no').removeAttr('required');
			$('#core_tercero_id').removeAttr('required');
		});
	</script>

	<script src="{{asset('assets/js/cxc_cancelaciones.js')}}"></script>
@endsection