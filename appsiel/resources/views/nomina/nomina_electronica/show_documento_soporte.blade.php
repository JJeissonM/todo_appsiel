<?php  
	$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');

	$color = 'black';

	$tipo_operacion = 'documento_soporte_nomina';
?>

@extends('transaccion.show')

@section('informacion_antes_encabezado')
	<div style="width: 100%; text-align: center;">
		<code>Nota: La visualización de este documento es diferente al documento enviado al cliente por el proveedor tecnológico.</code>	
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

    //dd($comprobante);
?>
	@include('nomina.nomina_electronica.tabla_visualizacion_envio_un_empleado',compact('comprobante'))
@endsection
