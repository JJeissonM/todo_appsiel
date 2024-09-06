<h4 class="card-header" style="text-align: center; width: 100%; background-color: #ddd; color: #636363;">{{$titulo}}</h4>
<p style="text-align: center;">
    <button class="btn btn-xs btn-primary" id="btn_envio_masivo" > <i class="fa fa-cogs"></i> Envío masivo </button>
</p>
<p>
    {{ Form::open(['url'=>'fe_envio_masivo', 'id'=>'form_envio_masivo']) }}
		<div class="alert alert-info" style="display: none;">
			<a href="#" id="close" class="close">&times;</a>
			<strong>Envío masivo de Documentos electrónicos</strong>
			<br>
			¿Desea cambiar la fecha para los docuentos anteriores a la fecha de HOY?
            <br>
			<label class="radio-inline"> <input type="radio" name="cambiar_fecha" value="1" id="opcion1" required="required">Si</label>
			<label class="radio-inline"> <input type="radio" name="cambiar_fecha" value="0" id="opcion2" required="required">No</label>
			<br>
			Para hacer el envío masivo, haga click en el siguiente botón: <small> <button class="btn btn-xs btn-primary" id="btn_enviar_documentos" data-url="{{ url('fe_envio_masivo') }}"> <i class="fa fa-send"></i> Enviar </button> </small>
		</div>
        
        <div style="padding:5px; display: none; text-align: center; color: red;" id="message_counting">
            Por favor espere.
            <br>
            Enviando doumentos... <span id="counter" style="color:#9c27b0"></span> restantes
        </div>

        <input name="vtas_doc_encabezados_ids_list" id="vtas_doc_encabezados_ids_list" type="hidden" value="">

	{{ Form::close() }}
</p>
<table class="table table-bordered table-responsive" id="tabla_documentos_pendientes">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th>Fecha</th>
            <th>Doc.</th>
            <th>Cliente</th>
            <th style="display: none;">ID</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $i = 1;
        ?>
        @foreach($facturas as $factura )
            <tr data-vtas_doc_encabezado_id="{{ $factura->id }}">
                <td>{{ $i }}</td>
                <td>{{ $factura->fecha }}</td>
                <td>{!! $factura->enlace_show_documento() !!}</td>
                <td>{{ number_format( $factura->tercero->numero_identificacion, 0, ',', '.' ) }} / {{ $factura->tercero->descripcion }}</td>
                <td style="display: none;">{{$factura->id}}</td>
            </tr>
            <?php 
                $i++;
            ?>
        @endforeach
    </tbody>
</table>