@extends('layouts.principal')

@section('content')

{{ Form::bsMigaPan($miga_pan) }}

<div class="col-md-12 botones-gmail">
	<div class="col-md-1">
		<select id="mostrar" onchange="mostrar()" class="form-control" style="color: #000 !important; font-size: 16px; width: 80px; position: absolute; float: left;">
			<option>Mostrar</option>
			<option value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option value="40">40</option>
			<option value="50">50</option>
			<option value="60">60</option>
			<option value="70">70</option>
			<option value="80">80</option>
			<option value="90">90</option>
			<option value="100">100</option>
			<option value="500">500</option>
		</select>
	</div>
	<div class="col-md-11">
		@if(isset($url_crear))
		@if( $url_crear != '' )
		{{ Form::bsBtnCreate( $url_crear ) }}
		@endif
		@endif
		@if(isset($url_ver))
		@if($url_ver!='')
		<a class="btn-gmail" onclick="verElement()" title="Consultar"><i class="fa fa-btn fa-eye"></i></a>
		@endif
		@endif
		@if(isset($url_print))
		@if($url_print!='')
		<a class="btn-gmail" onclick="imprimirElement()" title="Imprimir"><i class="fa fa-btn fa-print"></i></a>
		@endif
		@endif
		@if(isset($url_estado))
		@if($url_estado!='')
		<a class="btn-gmail" onclick="estadoElement()" title="Activar/Inactivar"><b>A/I</b></a>
		@endif
		@endif
		@if(isset($url_edit))
		@if($url_edit!='')
		<a class="btn-gmail" onclick="editElement()" title="Modificar"><i class="fa fa-btn fa-edit"></i></a>
		@endif
		@endif
		@if(isset($url_eliminar))
		@if($url_eliminar!='')
		<a class="btn-gmail" onclick="eliminarElement()" title="Eliminar"><i class="fa fa-trash"></i></a>
		@endif
		@endif
		@if(isset($botones))
		@foreach($botones as $boton)
		<a class="btn-gmail" id="{{$boton->url}}" onclick="botonElement(this.id)" title="{{$boton->title}}"><i class="fa fa-{{$boton->faicon}}"></i></a>
		@endforeach
		@endif

		<form action="{{route('export.table')}}" method="POST" id="exportForm" style="display: inline;" target='_blank'>
			<input type="hidden" name="sqlString" value="{{$sqlString}}" />
			<input type="hidden" name="tituloExport" value="{{$tituloExport}}" />
			<input type="hidden" name="search" value="{{$search}}" />
			{{ csrf_field() }}
			<a class="btn-gmail btn-pdf" id="btnPdf" onclick="exportPdf()" title="Exportar en PDF"><i class="fa fa-file-pdf-o"></i></a>
			<a class="btn-gmail btn-excel" id="btnExcel" onclick="exportExcel()" title="Exportar en Excel"><i class="fa fa-file-excel-o"></i></a>
		</form>
		<div class="search">
			<form class="form-horizontal" role="search" method="get" action="@if($source=='INDEX2') {{route('calificaciones.index2')}} @elseif($source=='BOLETIN') {{url('calificaciones/observaciones_boletin')}} @elseif($source=='INDEX3') {{url('academico_docente/asistencia_clases')}} @else {{route('web.index')}} @endif">
				<input type="hidden" name="id" value="{{$id_app}}" />
				<input type="hidden" name="id_modelo" value="{{$id_modelo}}" />
				@if(isset($url_complemento))
				<input type="hidden" name="curso_id" value="{{$curso->id}}" />
				<input type="hidden" name="asignatura_id" value="{{$asignatura->id}}" />
				@endif
				<input type="text" value="{{$search}}" name="search" style="color: #000 !important; font-size: 16px;" class="form-control input-sm" placeholder="Escriba aquí para buscar..." />
				<button style="position: absolute; height: 30px; right: 0; top: 5px; border-radius:2px;" class="btn btn-primary btn-xl" title="Consultar" type="submit"><i class="fa fa-search"></i></button>
			</form>
		</div>

	</div>


</div>


@include('layouts.mensajes')

@include('layouts.index.filtros')

<div class="table-responsive" id="table_content">
	<table class="table table-bordered table-striped table-hover" id="tbDatos">
		{{ Form::bsTableHeader($encabezado_tabla) }}
		<tbody>

			@foreach ($registros as $fila)
			<?php
			$totalElementos = count($fila->toArray());
			?>
			<tr>
				<td>
					<input type="checkbox" value="{{$fila['campo'.$totalElementos]}}" class="btn-gmail-check">
				</td>
				<?php for ($i = 1; $i < $totalElementos; $i++) { ?>
					<td class="table-text">
						<a href="{{url('').'/'.str_replace("id_fila", $fila['campo'.$totalElementos], $url_ver)}}" style="display: block; text-decoration: none;color: inherit;" title="Consultar">
							<div style="width: 100%;height: 100%;">
								{{ $fila['campo'.$i] }}
							</div>
						</a>
					</td>
				<?php } ?>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $registros->appends(['id' => $id_app,'id_modelo'=>$id_modelo,'nro_registros'=>$nro_registros,'search'=>$search,'curso_id'=> (isset($curso)) ? $curso->id : '','asignatura_id'=>(isset($asignatura)) ? $asignatura->id : ''])->links() }}
</div>
@endsection

@section('scripts')

<script type="text/javascript">
	$(document).ready(function() {

		$('input').first().focus();

	});



	function mostrar() {
		var nro = $("#mostrar").val();
		var source = "{{$source}}";
		if (source == 'INDEX2') {
			location.href = "{{url('')}}/calificaciones/index2?id={{$id_app}}&id_modelo={{$id_modelo}}&nro_registros=" + nro + "&search={{$search}}";
		} else if (source == 'BOLETIN') {
			location.href = "{{url('')}}/calificaciones/observaciones_boletin?id={{$id_app}}&id_modelo={{$id_modelo}}&nro_registros=" + nro + "&search={{$search}}";
		} else if (source == 'INDEX3') {
			location.href = "{{url('')}}/academico_docente/asistencia_clases?id={{$id_app}}&id_modelo={{$id_modelo}}&nro_registros=" + nro + "&search={{$search}}"+"<?php if(isset($url_complemento)){ echo $url_complemento; } ?>";
		} else {
			//INDEX1
			location.href = "{{url('')}}/web?id={{$id_app}}&id_modelo={{$id_modelo}}&nro_registros=" + nro + "&search={{$search}}";
		}
	}

	function verElement() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('').'/'.$url_ver}}";
			if (elementos.length == 1) {
				//ver uno
				url = url.replace('id_fila', elementos[0]);
				location.href = url.replace('&amp;', '&').replace('&amp;', '&');
			} else {
				//ver muchos
				elementos.forEach(function(item) {
					let url2 = url.replace('id_fila', item);
					window.open(url2.replace('&amp;', '&').replace('&amp;', '&'), '_blank');
				});
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function editElement() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('').'/'.$url_edit}}";
			if (elementos.length == 1) {
				//ver uno
				url = url.replace('id_fila', elementos[0]);
				location.href = url.replace('&amp;', '&').replace('&amp;', '&');
			} else {
				//ver muchos
				elementos.forEach(function(item) {
					let url2 = url.replace('id_fila', item);
					window.open(url2.replace('&amp;', '&').replace('&amp;', '&'), '_blank');
				});
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function eliminarElement() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('').'/'.$url_eliminar}}";
			if (elementos.length == 1) {
				//ver uno
				url = url.replace('id_fila', elementos[0]);
				location.href = url.replace('&amp;', '&').replace('&amp;', '&');
			} else {
				//ver muchos
				elementos.forEach(function(item) {
					let url2 = url.replace('id_fila', item);
					window.open(url2.replace('&amp;', '&').replace('&amp;', '&'), '_blank');
				});
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function estadoElement() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('').'/'.$url_estado}}";
			if (elementos.length == 1) {
				//ver uno
				url = url.replace('id_fila', elementos[0]);
				location.href = url.replace('&amp;', '&').replace('&amp;', '&');
			} else {
				//ver muchos
				elementos.forEach(function(item) {
					let url2 = url.replace('id_fila', item);
					window.open(url2.replace('&amp;', '&').replace('&amp;', '&'), '_blank');
				});
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function imprimirElement() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('').'/'.$url_print}}";
			if (elementos.length == 1) {
				//ver uno
				url = url.replace('id_fila', elementos[0]);
				location.href = url.replace('&amp;', '&').replace('&amp;', '&');
			} else {
				//ver muchos
				elementos.forEach(function(item) {
					let url2 = url.replace('id_fila', item);
					window.open(url2.replace('&amp;', '&').replace('&amp;', '&'), '_blank');
				});
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function botonElement(url) {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('').'/'}}" + url;
			if (elementos.length == 1) {
				//ver uno
				location.href = url.replace('id_fila', elementos[0]);
			} else {
				//ver muchos
				elementos.forEach(function(item) {
					let url2 = url.replace('id_fila', item);
					window.open(url2.replace('&amp;', '&').replace('&amp;', '&'), '_blank');
				});
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function mensaje(title, message, type) {
		Swal.fire(
			title,
			message,
			type
		)
	}

	function getElementos() {
		let elementos = [];
		$("input[type=checkbox]:checked").each(function() {
			elementos.push($(this).val());
		});
		return elementos;
	}

	function exportPdf() {
		$("#exportForm").append("<input type='hidden' name='tipo' value='PDF' />");
		$("#exportForm").submit();
	}

	function exportExcel() {
		$("#exportForm").append("<input type='hidden' name='tipo' value='EXCEL' />");
		$("#exportForm").submit();
	}

	function consultar() {
		mensaje('Hola!', 'Estamos trabajando en esta función, gracias por tu paciencia. ¡Pronto estará listo!', 'success');
	}
</script>

@if( isset($archivo_js) )
<script src="{{ asset( $archivo_js ) }}"></script>
@endif

@endsection