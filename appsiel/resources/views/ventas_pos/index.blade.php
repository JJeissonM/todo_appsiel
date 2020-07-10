<?php
	
	$user = \Auth::user();

	if ( !$user->hasRole('Cajedo PDV') ) 
    {
    	$pdvs = App\VentasPos\Pdv::all();
    }else{
    	$pdvs = App\VentasPos\Pdv::where( 'cajero_default_id', $user->id )
    								->get();
    }
?>


@extends('layouts.principal')

@section('estilos_2')
	<style>
		.tienda{

		}

		.tienda div.caja{
			border: 2px solid gray;
		    margin: -40px 10% 0px;
		    height: 200px;
		}

		.datos_pdv{
			padding: 10px;
		}

		table tr td {
			padding: 5px;
		}

	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<?php
				$cant_cols = 3;
				$i=$cant_cols;
		      ?>

			@foreach( $pdvs as $pdv )

				@if($i % $cant_cols == 0)
		            <div class="row">
		        @endif

		        	<?php 
		        		$num_facturas = 0;
		        		$apertura = App\VentasPos\AperturaEncabezado::where('pdv_id', $pdv->id)->get()->last();
		        		$cierre = App\VentasPos\CierreEncabezado::where('pdv_id', $pdv->id)->get()->last();

		        		$fecha_desde = '--';

		        		$btn_abrir = '<a href="' . url('web/create') . '?id=20&id_modelo=228&id_transaccion=45&pdv_id='.$pdv->id.'&cajero_id='.Auth::user()->id.'" class="btn btn-xs btn-success" > Apertura </a>';

		        		$btn_facturar = '<a href="' . url('pos_factura/create') . '?id=20&id_modelo=230&id_transaccion=47&pdv_id='.$pdv->id . '" class="btn btn-xs btn-primary" > Facturar </a>';

		        		$btn_cerrar = '<a href="' . url('web/create') . '?id=20&id_modelo=229&id_transaccion=46&pdv_id='.$pdv->id.'&cajero_id='.Auth::user()->id.'" class="btn btn-xs btn-danger" > Cierre </a>';

		        		$btn_acumular = '<button href="'.url('vtas_pos_acumular').'/'.$pdv->id.'" class="btn btn-xs btn-warning" id="btn_acumular" > Acumular </button>';

		        		$btn_contabilizar = '<button href="'.url('vtas_pos_contabilizar').'/'.$pdv->id.'" class="btn btn-xs btn-info" id="btn_acumular" > Contabilizar  </button>';

		        		$color = 'red';

		        		if ( $pdv->estado == 'Abierto' )
		        		{
		        			$color = 'green';

		        			$btn_abrir = '';
		        			$btn_acumular = '';
		        			$btn_contabilizar = '';

		        			if ( !is_null( $apertura ) )
		        			{
		        				$fecha_desde = $apertura->created_at;
		        			}
		        			

		        		}

		        		if ( $pdv->estado == 'Cerrado' )
		        		{
		        			$btn_cerrar = '';
		        			$btn_facturar = '';

		        			if ($num_facturas == 0)
		        			{
			        			$btn_acumular = '';
			        			$btn_contabilizar = '';
		        			}

		        			if ( !is_null( $cierre ) )
		        			{
		        				$fecha_desde = $cierre->created_at;
		        			}
		        		}
		        	?>


			     	<div class="col-sm-{{12/$cant_cols}} col-xs-{{12/$cant_cols}}" style="padding: 5px;">
		          		<div class="tienda">
							<p style="text-align: center; margin: 10px;">
								<img src="{{asset('assets/images/canopy_shop_pos.jpg') }}" style="display: inline; height: 120px; width: 100%;" />
							</p>
							<div class="caja">
								<div class="datos_pdv">

									<br>

									<table width="100%">
										<tr>
											<td style="text-align: center; font-size: 1.1em; font-weight: bold;" colspan="2">
												{{ $pdv->descripcion }}
												<hr>
											</td>
										</tr>
										<tr>
											<td>
												<b> Estado: </b>
											</td>
											<td>
												<i class="fa fa-circle" style="color: {{$color}}"> </i> {{ $pdv->estado }} <small> | desde {{ $fecha_desde }} </small>
											</td>
										</tr>
										<tr>
											<td>
												<b> # facturas: </b>
											</td>
											<td>
												<span class="badge">{{ $num_facturas }}</span>
											</td>
										</tr>
									</table>

									<div class="btn-group">
				
										{!! $btn_abrir !!}
										{!! $btn_facturar !!}
										{!! $btn_cerrar !!}
										{!! $btn_acumular !!}
										{!! $btn_contabilizar !!}
										
									</div>

								</div>										
							</div>
						</div>
			        </div>

				    <?php
				          $i++;
				      ?>
		          @if($i % $cant_cols == 0)
		            </div>
		            <br/><br/>
		          @endif

			@endforeach
		</div>
	</div>

@endsection