<h3 style="width: 100%; text-align: center;">Saldos de cuentas de resultados</h3> 
<h5 style="width: 100%; text-align: center;"><code>Desde: {{ $periodo_ejercicio->fecha_desde }} Hasta: {{ $periodo_ejercicio->fecha_hasta }} </code></h5>
<div class="table-responsive">
	<table class="table table-bordered table-striped">
		{{ Form::bsTableHeader(['Cuenta','Saldo','Mov.']) }}
		<tbody>
			<?php 
				$total_ganancias_peridas = 0;
			?>
			@foreach( $lista_movimientos as $mov_cuenta )
				<?php 
					$saldo = $mov_cuenta->valor_saldo;

					if( $saldo == 0 )
					{
						continue;
					}

					$lbl_mov_cta_resultado = 'DB';
					$lbl_mov_cta_cierre = 'CR';
					if( $saldo < 0 )
					{
						$saldo = $mov_cuenta->valor_saldo * -1;
						$lbl_mov_cta_resultado = 'CR';
						$lbl_mov_cta_cierre = 'DB';
					}

					$total_ganancias_peridas += $mov_cuenta->valor_saldo;
				?>
				<tr>
					<td class="text-center"> {{ $mov_cuenta->cuenta->codigo }} {{ $mov_cuenta->cuenta->descripcion }} </td>
					<td class="text-right"> {{ number_format( $saldo, 0, ',', '.') }} </td>
					<td> {{ $lbl_mov_cta_resultado }} </td>
				</tr>
				<tr>
					<td class="text-center"> {{ $cuenta_ganancias_perdidas_ejercicio->codigo }} {{ $cuenta_ganancias_perdidas_ejercicio->descripcion }} </td>
					<td class="text-right"> {{ number_format( $saldo, 0, ',', '.') }} </td>
					<td> {{ $lbl_mov_cta_cierre }} </td>
				</tr>
			@endforeach
		</tbody>

		<tfoot>
			<?php
				$total_ganancias_peridas = $total_ganancias_peridas * -1;
				$clase_fila = 'success';
				$lbl_resultado = 'Total Ganancias';
				if( $total_ganancias_peridas < 0 )
				{
					$clase_fila = 'danger';
					$lbl_resultado = 'Total Perdidas';
				}
			?>
			<tr class="{{ $clase_fila }}" align="center">
				<td colspan="3"> <b>{{ $lbl_resultado }}:</b> {{ number_format( $total_ganancias_peridas, 0, ',', '.') }} </td>
			</tr>
		</tfoot>

	</table>
</div>

<div class="container-fluid">
	{{ Form::open(['url'=>'contab_crear_nota_cierre_ejercicio','id'=>'form_create']) }}
		<input type="hidden" name="periodo_ejercicio_id2" id="periodo_ejercicio_id2" value="0">
		
		<div style="text-align: center;width: 100%;" id="div_form_promover">
			<span class="text-danger">Nota: Al presionar este botón se creará una Nota contable afectando todas las cuentas del listado con un movmiento opuesto al que se muestra; es decir, para las cuentas con saldo DB se les creará un registro CR y para las cuentas con saldo CR se les creará un registro DB.</span>
			
			<br><br>

			<button class="btn btn-info btn-lg" id="btn_promover"> <i class="fa fa-rocket"></i> Crear <br> Nota de cierre </button>
		</div>
		
	{{ Form::close() }}
</div>