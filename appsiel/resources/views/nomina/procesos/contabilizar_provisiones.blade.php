@extends('core.procesos.layout')

@section( 'titulo', 'Contabilización de provisiones de prestaciones sociales' )

@section('detalles')
	<p>
		Este proceso genera los registros contables de cada concepto de prestación social basandose en las cuentas Débito y Crédito asignadas en los parámetro de liquidación de cada concepto.
	</p>
	<br>
	<span class="text-info"> El proceso Genera un documento de contabilidad (Nota contable). </span>
	<br><br>
	<span class="text-danger"> Advertencia!!!. Antes de almacenar los registros, valide que aún no se haya contabilizado la provisión del mes.</span>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">


		<?php
			$tipo_transaccion = App\Sistema\TipoTransaccion::find( 9 );
			$tipo_docs_app = $tipo_transaccion->tipos_documentos;
            foreach ($tipo_docs_app as $fila)
            {
                $opciones[$fila->id] = $fila->prefijo . " - " . $fila->descripcion;
            }
		?>

		{{ Form::open(['url'=>'nom_contabilizar_provision_nomina','id'=>'formulario_inicial','files' => true]) }}
			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" >
					<span data-toggle="tooltip" title="Escoger día 30 del mes. Para febrero, escoger siempre día 28."> <i class="fa fa-question-circle"></i></span>
					<b> *Fecha fin de MES: </b> 
				</label>

				<div class="col-sm-8">
					{{ Form::date( 'fecha_final_promedios', date('Y-m-d'), [ 'class' => 'form-control', 'id' => 'fecha_final_promedios', 'required' => 'required' ]) }}
				</div>
			</div>

			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" >
					<b> *Tipo de Documento: </b> 
				</label>

				<div class="col-sm-8">
					{{ Form::select( 'core_tipo_doc_app_id', $opciones,null, [ 'class' => 'form-control', 'id' => 'core_tipo_doc_app_id', 'required' => 'required' ]) }}
				</div>
			</div>
							
			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" > <b> *Opciones de liquidación: </b> </label>

				<div class="col-sm-8">
					{{ Form::select( 'almacenar_registros', ['Previsualizar','Almacenar registros'],null, [ 'class' => 'form-control', 'id' => 'almacenar_registros' ]) }}
				</div>
			</div>

			<div class="row" style="padding:5px;">
				&nbsp;				 
			</div>

			<div class="row" style="padding:5px; text-align: center;">
				<div class="col-md-6">
					<button class="btn btn-success" id="btn_cargar"> <i class="fa fa-calculator"></i> Liquidar </button>
				</div>
				<div class="col-md-6">
					<button class="btn btn-danger" id="btn_retirar"> <i class="fa fa-trash"></i> Retirar </button>
				</div>
			</div>

				
		{{ Form::close() }}
	</div>


	{{ Form::bsBtnExcel( 'contabilizacion_documento_nomina' ) }}
	{{ Form::bsBtnPdf( 'contabilizacion_documento_nomina' ) }}

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#btn_cargar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
        		$("#div_resultado").html( '' );
				
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
			    });
		    });

			$("#btn_retirar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
        		$("#div_resultado").html( '' );

				var url = "{{ url('nom_retirar_contabilizacion_provision_nomina') }}" + '/' + $('#fecha_final_promedios').val();

				$.ajax({
				    url: url,
				    type: "get",
				    dataType: "html",
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
			    });
		    });

		});
	</script>
@endsection