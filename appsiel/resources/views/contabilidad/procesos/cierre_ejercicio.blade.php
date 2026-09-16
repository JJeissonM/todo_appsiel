@extends('core.procesos.layout')

@section( 'titulo', 'Cierre del ejercicio del periodo contable: Traslado de utilidades' )

@section('detalles')
	<p>
		Este proceso realiza el traslado de las utilidades o pérdidas del periodo respectivo, para esto hace la cancelación de cuentas de resultado, mediante el cual se deja en $0 todos los valores acumulados en las cuentas de Ingresos (4xx), Egresos (5xx) y Costos (6xx y 7xx) afectadas a lo largo del ejercicio contable, trasladando todos estos valores a la cuenta “59xxxx Ganancias o Perdidas del ejercicio”.
	</p>
	<p>
		Al final del proceso se crea un nota contable que contendrá los movimientos de cada cuenta afectada.
	</p>
	<p class="text-info">
		Nota: Luego de este proceso se debe generar una nota para cancelar el saldo de la cuenta “59xxxx Ganancias o Perdidas del ejercicio” contra la cuenta de patrimonio respectiva: "36xxxx Utilidad del ejercicio" o "36xxxx Perdida del ejercicio"
	</p>
	<br>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">

		<div class="row">
			<div class="col-md-12">

				<div class="marco_formulario">
					<div class="container-fluid">
						<h4>
							Parámetros de selección
						</h4>
						<hr>
						{{ Form::open(['url'=>'contab_generar_listado_cierre_ejercicio','id'=>'formulario_inicial']) }}
							<div class="row">
								<div class="col-sm-4">
									{{ Form::label('periodo_ejercicio_id','Periodo del ejercicio') }}
									<br/>
									{{ Form::select('periodo_ejercicio_id',\App\Contabilidad\ContabPeriodoEjercicio::opciones_campo_select(),null,[ 'class' => 'form-control', 'id' => 'periodo_ejercicio_id', 'required' => 'required' ]) }}
								</div>
								<div class="col-sm-3">
									&nbsp;
								</div>
								<div class="col-sm-3">
									&nbsp;
								</div>
								<div class="col-sm-2">
									{{ Form::label(' ','.') }}
									<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar listado</a>
								</div>
							</div>
						{{ Form::close() }}
					</div>
						
				</div>
					
			</div>
		</div>
				
	</div>

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			var generandoNotaCierre = false;

			$("#btn_generar").on('click',function(event){
		    	event.preventDefault();

				if (generandoNotaCierre) {
					return false;
				}

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		    	$('#div_form_cambiar').hide();

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
				$("#div_resultado").html('');

				var form = $('#formulario_inicial');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("formulario_inicial"));

				$.ajax({
				    url: url,
				    type: "post",
				    dataType: "html",
				    data: datos,
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
        			$("#checkbox_head").focus();
			    });
		    });


			$(document).on('submit', '#form_create', function(event){
				if (generandoNotaCierre || !validar_requeridos()) {
					event.preventDefault();
					return false;
				}

				if (!confirm('¿Está seguro de generar la Nota Contable para dejar en cero todas las cuentas de resultado del periodo contable ' + $('#periodo_ejercicio_id option:selected').text() + '?')) {
					event.preventDefault();
					return false;
				}

				$('#periodo_ejercicio_id2').val($('#periodo_ejercicio_id').val());
				generandoNotaCierre = true;
				var boton = $('#btn_promover');
				boton.data('contenido-original', boton.html());
				boton.prop('disabled', true)
					.attr('aria-busy', 'true')
					.html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Generando nota de cierre…');
				$('#periodo_ejercicio_id').prop('disabled', true);
				$('#btn_generar').addClass('disabled').attr('aria-disabled', 'true');
			});

			// Restaurar los controles al volver con el botón Atrás del navegador.
			$(window).on('pageshow', function(){
				if (!generandoNotaCierre) {
					return;
				}

				generandoNotaCierre = false;
				var boton = $('#btn_promover');
				boton.prop('disabled', false).removeAttr('aria-busy')
					.html(boton.data('contenido-original'));
				$('#periodo_ejercicio_id').prop('disabled', false);
				$('#btn_generar').removeClass('disabled').removeAttr('aria-disabled');
			});

		});
	</script>
@endsection