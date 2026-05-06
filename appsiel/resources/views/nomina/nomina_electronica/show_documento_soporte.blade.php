<?php  
	$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');

	$color = 'black';

	$tipo_operacion = 'documento_soporte_nomina';
?>

@extends('transaccion.show')

@section('informacion_antes_encabezado')
	<div style="width: 100%; text-align: center;">
		<code>Nota: La visualización de este documento es diferente al documento enviado al empleado por el proveedor tecnológico.</code>	
	</div>
	<br>
@endsection

@section('botones_acciones')

	@if( $doc_encabezado->estado != 'Sin enviar' && $doc_encabezado->estado != 'Contabilizado - Sin enviar' )
    	<a class="btn-gmail" href="{{ url( 'nom_electronica_consultar_documentos_emitidos/' . $doc_encabezado->id . '/' . $tipo_operacion . $variables_url ) }}" title="Representación gráfica (PDF)" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
	@endif

	<!-- MOSTRAR SOLO SI YA ESTA ENVIADO -->

	@if( $doc_encabezado->estado == 'Sin enviar' || $doc_encabezado->estado == 'Contabilizado - Sin enviar' )
		<?php 
			$color = 'red';
		?>
		@if( $doc_encabezado->estado == 'Sin enviar' )
			<a class="btn-gmail" href="#" id="btn_abrir_modal_recalcular_doc_soporte" title="Recalcular documento"><i class="fa fa-refresh"></i></a>
		@endif
        <a class="btn-gmail" href="{{ url('/nom_electronica_enviar_documentos') . '/[' . $doc_encabezado->id . ']' }}" class="btn btn-info btn-sm"title="Enviar"> <i class="fa fa-send"></i> </a>
        <i class="fa fa-circle" style="color: orange;"> Sin enviar </i>
	@endif

@endsection

@section('botones_imprimir_email')
	&nbsp;
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'nom_electronica_show_doc_soporte/', $variables_url ) !!}
@endsection

@section('documento_vista')
<?php 
    $comprobante = $doc_encabezado->toArray();
    $comprobante['empleado'] = $doc_encabezado->empleado;
    $comprobante['accruals'] = $comprobante['accruals_json'];
    $comprobante['deductions'] = $comprobante['deductions_json'];
    $comprobante['employee'] = $comprobante['employee_json'];
?>
	@include('nomina.nomina_electronica.tabla_visualizacion_envio_un_empleado',compact('comprobante'))
@endsection

@section('section_after_documento_vista')
	@if( $doc_encabezado->estado == 'Sin enviar' )
		<div class="modal fade" id="modal_recalcular_doc_soporte" tabindex="-1" role="dialog" aria-labelledby="modal_recalcular_doc_soporte_label" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					{{ Form::open(['url' => url('nom_electronica_recalcular_doc_soporte/' . $doc_encabezado->id . $variables_url), 'id' => 'form_recalcular_doc_soporte']) }}
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="modal_recalcular_doc_soporte_label">Confirmar recálculo</h4>
						</div>
						<div class="modal-body">
							<p>Se recalcularán los datos de cabecera, devengos, deducciones y empleado para {{ $doc_encabezado->get_value_to_show() }}.</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" id="btn_cancelar_recalculo_doc_soporte" data-dismiss="modal">
								<span class="btn-text">Cancelar</span>
								<i class="fa fa-spinner fa-spin btn-spinner" style="display: none; margin-left: 6px;"></i>
							</button>
							<button type="submit" class="btn btn-primary" id="btn_confirmar_recalculo_doc_soporte">
								<span class="btn-text">Recalcular</span>
								<i class="fa fa-spinner fa-spin btn-spinner" style="display: none; margin-left: 6px;"></i>
							</button>
						</div>
					{{ Form::close() }}
				</div>
			</div>
		</div>
	@endif
@endsection

@section('otros_scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#btn_abrir_modal_recalcular_doc_soporte').on('click', function(event){
				event.preventDefault();
				$('#modal_recalcular_doc_soporte').modal('show');
			});

			$('#btn_cancelar_recalculo_doc_soporte').on('click', function(){
				var btn = $(this);
				btn.find('.btn-spinner').show();
				setTimeout(function(){
					btn.find('.btn-spinner').hide();
				}, 300);
			});

			$('#form_recalcular_doc_soporte').on('submit', function(){
				var btn = $('#btn_confirmar_recalculo_doc_soporte');
				btn.prop('disabled', true);
				btn.find('.btn-text').text('Recalculando...');
				btn.find('.btn-spinner').show();
				$('#btn_cancelar_recalculo_doc_soporte').prop('disabled', true);
			});
		});
	</script>
@endsection
