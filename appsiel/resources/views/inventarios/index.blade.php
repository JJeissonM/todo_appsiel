@extends('layouts.principal')

@section('content')
	<style>
		.resumen-bodegas {
			display: flex;
			flex-wrap: wrap;
			gap: 16px;
			align-items: stretch;
		}

		.resumen-bodega {
			border: 1px solid #ddd;
			border-radius: 4px;
			flex: 1 1 320px;
			min-width: 280px;
			background: #fff;
		}

		.resumen-bodega__encabezado {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			padding: 12px 14px;
			background-color: #50B794;
			border-bottom: 1px solid #43a883;
		}

		.resumen-bodega__titulo {
			font-weight: 700;
			line-height: 1.3;
			color: #fff;
		}

		.resumen-bodega__accion {
			flex: 0 0 auto;
			font-size: 16px;
			color: #fff;
		}

		.resumen-bodega__accion:hover,
		.resumen-bodega__accion:focus {
			color: #fff;
		}

		.resumen-bodega__tabla {
			width: 100%;
			margin-bottom: 0;
		}

		.resumen-bodega__tabla th,
		.resumen-bodega__tabla td {
			padding: 9px 14px !important;
			vertical-align: middle !important;
		}

		.resumen-bodega__tabla td {
			text-align: right;
			font-weight: 700;
		}

		@media (max-width: 640px) {
			.resumen-bodegas {
				display: block;
			}

			.resumen-bodega {
				margin-bottom: 14px;
				min-width: 0;
			}
		}
	</style>

	{{ Form::bsMigaPan($miga_pan) }}
	<!-- { !! $select_crear !!} -->
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<h4>Resumen por bodega <small>({{ count($resumen_bodegas) }})</small></h4>
			<hr>
			<br/>

			@if( count($resumen_bodegas) == 0 )
				<div class="alert alert-info">
					No hay bodegas registradas.
				</div>
			@else
				<div class="resumen-bodegas">
					@foreach ($resumen_bodegas as $resumen)
						<div class="resumen-bodega">
							<div class="resumen-bodega__encabezado">
								<div class="resumen-bodega__titulo">{{ $resumen->bodega_nombre }}</div>
								<a class="resumen-bodega__accion" href="{{ url('inv_consultar_existencias/'.$resumen->bodega_id.'?id='.Input::get('id')).'&fecha_corte='.date('Y-m-d') }}" title="Consultar existencias">
									<i class="fa fa-search"></i>
								</a>
							</div>
							<table class="table table-striped table-condensed resumen-bodega__tabla">
								<tbody>
									<tr>
										<th>Total con existencia</th>
										<td>{{ number_format($resumen->total_con_existencia, 0, ',', '.') }}</td>
									</tr>
									<tr>
										<th>Total en cero</th>
										<td>{{ number_format($resumen->total_en_cero, 0, ',', '.') }}</td>
									</tr>
									<tr style="color: #e86a6a;">
										<th>Total negativos</th>
										<td>{{ number_format($resumen->total_negativos, 0, ',', '.') }}</td>
									</tr>
									<tr style="color: #f2d200;">
										<th>Total con bajo stock mínimo</th>
										<td>{{ number_format($resumen->total_bajo_minimo, 0, ',', '.') }}</td>
									</tr>
								</tbody>
							</table>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	</div>

	<br/>
@endsection
