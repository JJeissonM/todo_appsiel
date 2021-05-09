

<h5>Ingreso de datos para nuevos cheques</h5>
<hr>

<div class="row">
	<div class="col-md-6" >
		<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
			<h6>Formulario</h6>
			<hr>
			<div class="row">				
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsFecha( 'fecha_emision', date('Y-m-d'), 'Fecha emisión', [], []) }}
					</div>
				</div>
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsFecha( 'fecha_cobro', date('Y-m-d'), 'Fecha cobro', [], []) }}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsText( 'numero_cheque', null, 'Número de cheque', []) }}
					</div>
				</div>
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsText( 'referencia_cheque', null, 'Referencia	', []) }}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsSelect('entidad_financiera_id', null, 'Entidad financiera', $entidades_financieras, []) }}
					</div>
				</div>
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsText( 'valor_cheque', null, 'Valor', []) }}
					</div>
				</div>
			</div>
			<p style="text-align: center;">
				<button class="btn btn-primary" id="btn_agregar_cheque"> Agregar </button>
			</p>
		</div>
		
	</div>
	<div class="col-md-6">
		<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
			<div class="row">
				<div class="col-md-12">
					<h6>Cheques ingresados</h6>
					<hr>
					<table class="table table-striped table-bordered" id="tabla_registros_cheques">
						<thead>
							<tr>
								<th style="display: none;">entidad_financiera_id</th>
								<th style="display: none;">valor_cheque</th>
								<th data-override="fecha_emision">F. Emisión</th>
								<th data-override="fecha_cobro">F. cobro</th>
								<th data-override="numero_cheque">Núm.</th>
								<th data-override="referencia_cheque">Ref.</th>
								<th>Banco</th>
								<th>Valor</th>
								<th></th>
							</tr>
						</thead>
						<tbody>				
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5">&nbsp;</td>
								<td id="valor_total_cheques" align="right">$ 0</td>
								<td><input type="hidden" name="input_valor_total_cheques" id="input_valor_total_cheques" value="0"></td>
							</tr>
						</tfoot>
					</table>
				</div>
		</div>
		</div>
		
	</div>
</div>



@section('scripts2')
	<script type="text/javascript">

		$(document).ready(function(){

			$(document).on('click', '#btn_agregar_cheque', function(event) 
			{
				event.preventDefault();

				if( !validar_requeridos() )
				{
					return false;
				}

				var string_fila = generar_string_celdas();

				$('#tabla_registros_cheques').find('tbody:last').append('<tr class="linea_registro_cheque">' + string_fila + '</tr>');
				
				hay_cheques++;
				console.log(hay_cheques);

				resetear_campos_cheque();

				calcular_totales_cheques();

				$('#numero_cheque').focus();
			});

			$(document).on('click', '.btn_eliminar_cheque', function(event) 
			{
				var fila = $(this).closest("tr");

				fila.remove();
				
				hay_cheques--;
				console.log(hay_cheques);

				calcular_totales_cheques();

			});

			$('#valor_cheque').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#btn_agregar_cheque').focus();
				}
			});

			$('#numero_cheque').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#referencia_cheque').focus();
				}
			});

			$('#referencia_cheque').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#valor_cheque').focus();
				}
			});

			function calcular_totales_cheques()
			{
				var valor_total_cheques = 0.0;
				$('.linea_registro_cheque').each(function()
				{
				    valor_total_cheques += parseFloat( $(this).find('.valor_cheque').text() );
				});

				$('#valor_total_cheques').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_cheques.toFixed(2) ) );
				$('#input_valor_total_cheques').val(valor_total_cheques);

			}

			function resetear_campos_cheque()
			{
				$('#valor_cheque').val('');
				$('#numero_cheque').val('');
				$('#referencia_cheque').val('');
				$('#entidad_financiera_id').val('');
			}

			function validar_requeridos()
			{
				if( $('#numero_cheque').val() == '' )
				{
					alert('Faltan datos por llenar del cheque.')
					$('#numero_cheque').focus();
					return false;
				}

				if( $('#valor_cheque').val() == '' || $('#valor_cheque').val() == '0' )
				{
					alert('Faltan datos por llenar del cheque.')
					$('#valor_cheque').focus();
					return false;
				}

				if( !validar_input_numerico( $('#valor_cheque') ) )
				{
					return false;
				}

				return true;
			}

			function generar_string_celdas()
			{
				var celdas = [];
				var num_celda = 0;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#entidad_financiera_id').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"> <div class="valor_cheque">'+ $('#valor_cheque').val() +'</div></td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#fecha_emision').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#fecha_cobro').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#numero_cheque').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#referencia_cheque').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#entidad_financiera_id option:selected').text() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td align="right"> '+ '$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_cheque').val() ) + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_cheque'><i class='fa fa-btn fa-trash'></i></button>";
				celdas[ num_celda ] = '<td>' + btn_borrar + '</td>';

				var cantidad_celdas = celdas.length;
				var string_celdas = '';
				for (var i = 0; i < cantidad_celdas; i++)
				{
					string_celdas = string_celdas + celdas[i];
				}

				return string_celdas;
			}

		});
	</script>
@endsection
			
	            			