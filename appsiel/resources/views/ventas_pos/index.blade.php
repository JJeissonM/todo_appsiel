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
		        		
		        		$num_facturas = App\VentasPos\FacturaPos::where('pdv_id', $pdv->id)->where('estado', 'Pendiente')->count();
		        		
		        		$fecha_primera_factura = date('Y-m-d');
		        		$primera_factura = App\VentasPos\FacturaPos::where('pdv_id', $pdv->id)->where('estado', 'Pendiente')->first();
		        		if ( !is_null($primera_factura ) )
		        		{
		        			$fecha_primera_factura = $primera_factura->fecha;
		        		}
		        		$fecha_hoy = date('Y-m-d');



		        		$apertura = App\VentasPos\AperturaEncabezado::where('pdv_id', $pdv->id)->get()->last();
		        		$cierre = App\VentasPos\CierreEncabezado::where('pdv_id', $pdv->id)->get()->last();

		        		$fecha_desde = '--';

		        		$btn_abrir = '<a href="' . url('web/create') . '?id=20&id_modelo=228&id_transaccion=45&pdv_id='.$pdv->id.'&cajero_id='.Auth::user()->id.'" class="btn btn-xs btn-success" > Apertura </a>';

		        		$btn_facturar = '<a href="' . url('pos_factura/create') . '?id=20&id_modelo=230&id_transaccion=47&pdv_id='.$pdv->id . '" class="btn btn-xs btn-primary" > Facturar </a>';

		        		$btn_cerrar = '<a href="' . url('web/create') . '?id=20&id_modelo=229&id_transaccion=46&pdv_id='.$pdv->id.'&cajero_id='.Auth::user()->id.'" class="btn btn-xs btn-danger" > Cierre </a>';

		        		$btn_acumular = '<button class="btn btn-xs btn-warning btn_acumular" data-pdv_id="'.$pdv->id.'" data-pdv_descripcion="'.$pdv->descripcion.'"  > Acumular </button>';

		        		$btn_hacer_arqueo = '<a href="'.url( '/web/create' . '?id=20&id_modelo=158&vista=tesoreria.arqueo_caja.create&teso_caja_id='.$pdv->caja_default_id ) .'" class="btn btn-xs btn-info" id="btn_hacer_arqueo"> Hacer arqueo </a>';

		        		//$btn_consultar_estado = '<button class="btn btn-primary btn-xs btn_consultar_estado_pdv" data-pdv_id="'.$pdv->id.'" data-lbl_ventana="Ingresos"> <i class="fa fa-btn fa-search"></i> Estado PDV </button>';
		        		$btn_consultar_estado = '';

		        		$color = 'red';

		        		if ( $pdv->estado == 'Abierto' )
		        		{
		        			$color = 'green';

		        			$btn_abrir = '';
		        			$btn_acumular = '';
		        			$btn_hacer_arqueo = '';

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
			        			//$btn_hacer_arqueo = '';
		        			}

		        			if ( !is_null( $cierre ) )
		        			{
		        				$fecha_desde = $cierre->created_at;
		        			}
		        		}
		        	?>

			     	<div class="col-md-{{12/$cant_cols}} col-xs-12 col-sm-12" style="padding: 5px;">
		          		<div class="tienda">
							<p style="text-align: center; margin: 10px;">
								<img src="{{asset('assets/images/canopy_shop_pos.jpg') }}" style="display: inline; height: 120px; width: 100%;" />
							</p>
							<div class="caja">
								<div class="datos_pdv">

									<br>

									<div class="table-responsive">
										<div class="table">
											
												<div style="text-align: center; font-size: 1.1em; font-weight: bold;" colspan="2">
													{{ $pdv->descripcion }}
													<hr>
												</div>
											
											
												<div>
													<b> Estado: </b> <i class="fa fa-circle" style="color: {{$color}}"> </i> {{ $pdv->estado }} <small> | desde {{ $fecha_desde }} </small>
												</div>
											
											
												<div>
													<b> # facturas: </b>
													<span class="badge">{{ $num_facturas }}</span>
													@if( $num_facturas > 0 )
														<button style="background: transparent; border: 0px; text-decoration: underline; color: #069;" class="btn_consultar_facturas" href="#" data-pdv_id="{{$pdv->id}}" data-lbl_ventana="Facturas de ventas" data-fecha_primera_factura="{{$fecha_primera_factura}}" data-fecha_hoy="{{$fecha_hoy}}"> Consultar </button>
													@endif
												</div>
											
										</div>
										<div class="btn-group">
					
											{!! $btn_abrir !!}
											{!! $btn_facturar !!}
											{!! $btn_cerrar !!}
											{!! $btn_acumular !!}
											{!! $btn_hacer_arqueo !!}
											
										</div>
										<br><br>
										{!! $btn_consultar_estado !!}
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

	@include('components.design.ventana_modal',['titulo'=>'','texto_mensaje'=>''])

	@include( 'components.design.ventana_modal2',[ 'titulo2' => '', 'texto_mensaje2' => '', 'clase_tamanio' => 'modal-lg' ] )

@endsection

@section('scripts')

	<script type="text/javascript">
		
		var pdv_id;

		$(document).ready(function(){

			$(".btn_acumular").click(function(event){

				pdv_id = $(this).attr('data-pdv_id');

		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();
		        $(".btn_save_modal").hide();

		        $('#contenido_modal').html( '<h1> Acumulando facturas POS... </h1>' );

		        var url = "{{url('pos_factura_acumular')}}" + "/" + pdv_id;

				$.get( url )
					.done(function( data ) {
							
						$('#contenido_modal').html( '<h1>Acumulación completada exitosamente. </h1> <h1> Contabilizando documentos... </h1>' );

						// Nueva petición AJAX
						var url_2 = "{{url('pos_factura_contabilizar')}}" + "/" + pdv_id;

						$.get( url_2 )
							.done(function( data ) {
									
								$('#contenido_modal').html( '<h1>Acumulación completada exitosamente. </h1> <h1> Contabilización completada exitosamente. </h1>' );
								
				                $("#div_spin").hide();

				                location.reload();

							});

					});		        
		    });
		    



			$(document).on('click',".btn_consultar_facturas",function(event){
				event.preventDefault();

		        $('#contenido_modal2').html('');
				$('#div_spin2').fadeIn();

		        $("#myModal2").modal(
		        	{backdrop: "static"}
		        );

		        $("#myModal2 .modal-title").text('Consulta de ' + $(this).attr('data-lbl_ventana'));

		        $("#myModal2 .btn_edit_modal").hide();
				$("#myModal2 .btn_save_modal").hide();
		        
		        var url = "{{ url('pos_consultar_documentos_pendientes') }}" + "/" + $(this).attr('data-pdv_id') + "/" + $(this).attr('data-fecha_primera_factura') + "/" + $(this).attr('data-fecha_hoy');

		        $.get( url, function( respuesta ){
		        	$('#div_spin2').hide();
		        	$('#contenido_modal2').html( respuesta );
		        });/**/
		    });

		});
	</script>
@endsection