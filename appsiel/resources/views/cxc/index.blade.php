@extends('layouts.principal')

<?php use App\Http\Controllers\Core\ConfiguracionController; ?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	{!! $select_crear !!}
	<hr>

	@include('layouts.mensajes')
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<?php 
					echo Lava::render('PieChart', 'torta_cartera', 'torta_cartera');
				?>
			<h2><b>Estado de cuentas pendientes</b></h2>
			<div class="row">
				<div class="col-md-12">
					{{ Form::bsSelect('empresa_id',$empresa_id,'Empresa',$empresas,['id'=>'empresa_id']) }}
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<h3>Cartera X Edades</h3>
					<table>  
					  	<tr>
					    	<td> 
					        	1 
					        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					        	<span id="lbl_edad_1">{{ $edades[1] }}</span>
					        </td>
					    	<td> 
					        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					        	<span id="lbl_edad_2">{{ $edades[2] }}</span>
					        </td>
					    	<td> 
					        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					        	<span id="lbl_edad_3">{{ $edades[3] }}</span>
					        </td>
					    	<td> 
					        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					        	<span id="lbl_edad_4">{{ $edades[4] }}</span>
					        </td>
					    </tr>
					    
					  	<tr>
					    	<td> 
					        	<input type="range" id="edad_1" min="1" max="180" value="{{ $edades[1] }}">
					        </td>
					    	<td> 
					        	<input type="range" id="edad_2" min="{{ $edades[1] + 1 }}" max="360" value="{{ $edades[2] }}">
					        </td>
					    	<td> 
					        	<input type="range" id="edad_3" min="{{ $edades[2] + 1 }}" max="500" value="{{ $edades[3] }}">
					        </td>
					    	<td> 
					        	<input type="range" id="edad_4" min="{{ $edades[3] + 1 }}" max="999" value="{{ $edades[4] }}">
					        </td>
					    	<td> 
					        	<a href="{{ url('cxc?id='.Input::get('id')) }}" class="btn btn-info btn-xs" id="btn_actualizar">Actualizar</a>
					        </td>
					    </tr>
					  </table>
					<div id="torta_cartera"></div>
				</div>
				<div class="col-md-6">
					<h3>&nbsp;</h3>
					<div class="table-responsive">
						<table class="table table-bordered table-striped">
							{{ Form::bsTableHeader(['Edad','Valor','Acción']) }}
							<tbody>								
									<?php

									$cantidad = count($cartera_edades);
									
									$total_cartera = 0;
									for($i=1; $i <= $cantidad;$i++)
									{
							            $lbl = explode(" ",$cartera_edades[$i]['lbl']);

							            $url='cxc/imprimir_cartera_una_edad/'.((int)$lbl[0]-1).'/'.$lbl[2].'/'.$empresa_id;
							            echo "<tr>
							            <td class='text-center'>".$cartera_edades[$i]['lbl']."</td>
							             <td>$".number_format($cartera_edades[$i]['saldo_pendiente'], 0, ',', '.')."</td>";
							             	echo "<td><a class='btn btn-info btn-xs btn-detail' href='".url($url)."'><i class='fa fa-btn fa-print'></i>&nbsp;Imprimir cartera</a></td></tr>";
							            $total_cartera+=$cartera_edades[$i]['saldo_pendiente'];
							        }
							        ?>
							        <tr>
							        	<td><b>Total cartera</b></td>
							        	<td class="text-right">${{ number_format($total_cartera, 0, ',', '.') }}</td>
							        	<td></td>
							        </tr>								
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			var id = getParameterByName('id');

			$('#edad_1').on('input',function(){
				var valor = $(this).val();
				$('#lbl_edad_1').text(valor);
				var min = parseInt(valor) + 1;
				$('#edad_2').attr('min',min);
				
				//console.log($('#edad_2').val() + " " + parseInt(valor));

				if($('#edad_2').val() < parseInt(valor)){
					var nuevo_valor = parseInt(valor) + 1;
					$('#edad_2').val(nuevo_valor);
					$('#lbl_edad_2').text(nuevo_valor);
				}

				$('#btn_actualizar').attr('href','cxc?id='+id+'&e1='+$('#edad_1').val()+'&e2='+$('#edad_2').val()+'&e3='+$('#edad_3').val()+'&e4='+$('#edad_4').val()+'&empresa_id='+$('#empresa_id').val());
			});

			$('#edad_2').on('input',function(){
				var valor = $(this).val();
				$('#lbl_edad_2').text(valor);
				var min = parseInt(valor) + 1;
				$('#edad_3').attr('min',min);

				if($('#edad_3').val() < parseInt(valor)){
					var nuevo_valor = parseInt(valor) + 1;
					$('#edad_3').val(nuevo_valor);
					$('#lbl_edad_3').text(nuevo_valor);
				}

				$('#btn_actualizar').attr('href','cxc?id='+id+'&e1='+$('#edad_1').val()+'&e2='+$('#edad_2').val()+'&e3='+$('#edad_3').val()+'&e4='+$('#edad_4').val()+'&empresa_id='+$('#empresa_id').val());
			});

			$('#edad_3').on('input',function(){
				var valor = $(this).val();
				$('#lbl_edad_3').text(valor);
				var min = parseInt(valor) + 1;
				$('#edad_4').attr('min',min);

				if($('#edad_4').val() < parseInt(valor)){
					var nuevo_valor = parseInt(valor) + 1;
					$('#edad_4').val(nuevo_valor);
					$('#lbl_edad_4').text(nuevo_valor);
				}

				$('#btn_actualizar').attr('href','cxc?id='+id+'&e1='+$('#edad_1').val()+'&e2='+$('#edad_2').val()+'&e3='+$('#edad_3').val()+'&e4='+$('#edad_4').val()+'&empresa_id='+$('#empresa_id').val());
			});

			$('#edad_4').on('input',function(){
				var valor = $(this).val();
				$('#lbl_edad_4').text(valor);

				$('#btn_actualizar').attr('href','cxc?id='+id+'&e1='+$('#edad_1').val()+'&e2='+$('#edad_2').val()+'&e3='+$('#edad_3').val()+'&e4='+$('#edad_4').val()+'&empresa_id='+$('#empresa_id').val());
			});

			$('#empresa_id').on('change',function(){
				$('#btn_actualizar').attr('href','cxc?id='+id+'&e1='+$('#edad_1').val()+'&e2='+$('#edad_2').val()+'&e3='+$('#edad_3').val()+'&e4='+$('#edad_4').val()+'&empresa_id='+$('#empresa_id').val());
			});

			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}

		});

	</script>
@endsection