@extends('layouts.principal')
@section('estilos_1')
<style>
#cert-tercero .custom-combobox, #cert-firma-autorizada .custom-combobox { width: calc(100% - 24px); }
#cert-tercero .custom-combobox-input, #cert-firma-autorizada .custom-combobox-input { width: 100%; box-sizing: border-box; }
.cert-tercero-opciones { max-height: 280px; overflow-y: auto; overflow-x: hidden; }
</style>
@endsection
@section('content')
<div class="container-fluid"><h2>Certificados de Retefuente e ICA</h2>
<p>Retefuente: certificado anual de otros conceptos o parcial a solicitud del beneficiario. ICA: seleccione la jurisdicción y su norma municipal. Esta opción certifica compras; los certificados laborales se tramitan en Nómina.</p>
<form id="cert-filtros" method="POST" enctype="multipart/form-data" action="{{ url('contab_certificados_retenciones/previsualizar') }}">
{{ csrf_field() }}
<div class="row">
<div class="col-md-3"><label>Impuesto</label><select name="tipo_impuesto" id="cert-impuesto" class="form-control"><option value="FUENTE">Retefuente</option><option value="ICA">ICA</option></select></div>
<div class="col-md-3"><label>Período</label><select name="periodo" id="cert-periodo" class="form-control"><option value="anual">Anual</option><option value="bimestral">Bimestral (dos meses)</option><option value="fechas">Entre fechas</option></select></div>
<div class="col-md-3 cert-calendario"><label>Año</label><input name="anio" type="number" min="2000" max="2100" value="{{ date('Y') }}" class="form-control" required></div>
<div class="col-md-3" id="cert-bimestre"><label>Bimestre</label><select name="bimestre" class="form-control">@foreach([1=>'Enero–febrero',2=>'Marzo–abril',3=>'Mayo–junio',4=>'Julio–agosto',5=>'Septiembre–octubre',6=>'Noviembre–diciembre'] as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
</div>
<div class="row" id="cert-fechas"><div class="col-md-3"><label>Desde</label><input type="date" name="fecha_desde" class="form-control"></div><div class="col-md-3"><label>Hasta</label><input type="date" name="fecha_hasta" class="form-control"></div></div>
<div class="row">
<div class="col-md-3"><label>Proveedores</label><select id="cert-alcance" class="form-control"><option value="todos">Todos con operaciones</option><option value="tercero">Un tercero</option><option value="grupo">Grupo de proveedores</option></select></div>
<div class="col-md-3" id="cert-tercero"><label for="cert-tercero-busqueda">Tercero</label><select name="tercero_id" id="cert-tercero-id" class="form-control combobox"><option value="">Seleccione</option>@foreach($terceros as $t)<option value="{{ $t->id }}">{{ $t->descripcion }} ({{ $t->numero_identificacion }})</option>@endforeach</select></div>
<div class="col-md-3" id="cert-grupo"><label>Grupo (clase de proveedor)</label><select name="grupo_id" class="form-control"><option value="">Seleccione</option>@foreach($grupos as $g)<option value="{{ $g->id }}">{{ $g->descripcion }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Compras incluidas</label><select name="compras" class="form-control"><option value="retenidas">Documentos con retención</option><option value="todas">Todas, incluso sin retención</option></select></div>
</div>
<div class="row"><div class="col-md-4"><label>Ciudad donde se consignó la retención</label><input name="ciudad_consignacion" maxlength="150" class="form-control" required></div>
<div class="col-md-8" id="cert-firma-autorizada"><label for="cert-firma-busqueda">Firma autorizada</label><select name="firma_autorizada_id" id="cert-firma-id" class="form-control combobox"><option value="">Seleccione</option>@foreach($firmas as $firma)<option value="{{ $firma->id }}">{{ $firma->descripcion }} ({{ $firma->numero_identificacion }}) — {{ $firma->titulo_tercero }}</option>@endforeach</select>
<small>El nombre, la identificación y la imagen se toman del catálogo de firmas autorizadas. Para firma manual e ICA debe tener una firma adjunta.</small></div></div>
<div class="row"><div class="col-md-4"><label>Firma para Retefuente</label><select name="firma_fuente" class="form-control"><option value="manual">Firma del responsable</option><option value="computador">Generado por computador (retenedor persona jurídica)</option></select></div>
</div>
<fieldset id="cert-ica"><legend>Jurisdicción de ICA</legend><div class="row">
<div class="col-md-3"><label>Código DANE</label><input name="codigo_municipio" pattern="[0-9]{5}" maxlength="5" class="form-control"></div>
<div class="col-md-3"><label>Municipio</label><input name="municipio" maxlength="150" class="form-control"></div>
<div class="col-md-6"><label>Norma municipal aplicable (acuerdo / artículo)</label><input name="norma_ica" maxlength="250" class="form-control"></div></div>
<label><input type="checkbox" name="confirmar_municipio" value="1"> Confirmo que las retenciones históricas sin municipio registrado corresponden al municipio seleccionado.</label></fieldset>
<p><button class="btn btn-primary" id="cert-previsualizar">Previsualizar certificados</button> <span id="cert-cargando" role="status"></span></p>
</form>
<div id="cert-error" class="alert alert-danger" style="display:none" role="alert"></div><div id="cert-resultados"></div>
<h3>Últimos certificados emitidos por usted</h3>
<table class="table"><thead><tr><th>Número</th><th>Proveedor</th><th>Impuesto / período</th><th>Documento</th></tr></thead><tbody>
@foreach($historial as $h)<tr><td>{{ $h->id }}</td><td>{{ $h->proveedor }}</td><td>{{ $h->impuesto }} {{ $h->fecha_desde }} / {{ $h->fecha_hasta }}</td><td><a target="_blank" rel="noopener" href="{{ url('contab_certificados_retenciones/'.$h->token) }}">Ver emitido</a>
<form style="display:inline" method="POST" target="_blank" action="{{ url('contab_certificados_retenciones/'.$h->token.'/pdf') }}">{{ csrf_field() }}<button class="btn btn-default btn-sm">Descargar PDF</button></form>
<button class="btn btn-default btn-sm cert-enviar-historial" data-token="{{ $h->token }}">Enviar por correo</button><span class="cert-historial-estado"></span></td></tr>@endforeach
</tbody></table></div>
@endsection
@section('scripts')
<script src="{{ asset('assets/js/contabilidad/certificados_retenciones.js') }}"></script>
@endsection
