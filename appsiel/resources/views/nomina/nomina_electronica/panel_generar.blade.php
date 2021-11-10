<h4> Genarción y envío de Documentos de soporte de Nómina Electrónica</h4>
<p>
	Por esta opción, el sistema genera los documentos de soporte de nómina electrónica.
	<br>
	Un documento por cada empleado que tenga movimiento en el periodo selecionado.
	<br>
	Puede escoger la opción <b>Previsualizar</b> para revisar los documentos antes de hacer el envío hacia el proveedor tecnológico y la DIAN.
	@if( $msj_advertencia != '' )
		<div class="alert alert-danger">
		  <strong>Advertencia!</strong> {{ $msj_advertencia }}
		</div>
	@endif
</p>

<div class="row" id="div_formulario">
	{{ Form::open(['url'=>'nom_electronica_generar_doc_soporte','id'=>'formulario_inicial','files' => true]) }}

		<div class="row" style="padding:5px;">
			<label class="control-label col-sm-4" > <b> *Opciones de generación: </b> </label>
			<div class="col-sm-8">
				{{ Form::select( 'almacenar_registros', ['Previsualizar','Enviar documentos'],null, [ 'class' => 'form-control', 'id' => 'almacenar_registros' ]) }}
			</div>
		</div>

		<div class="row" style="padding:5px;">
			<label class="control-label col-sm-4" > <b> Fecha final periodo a generar: </b> </label>

			<div class="col-sm-8">
				{{ Form::date( 'fecha_final_periodo',null, [ 'class' => 'form-control', 'id' => 'fecha_final_periodo', 'required' => 'required' ]) }}
			</div>
		</div>

		<div class="row" style="padding:5px;">
			&nbsp;				 
		</div>
			
	{{ Form::close() }}

	@if( $msj_advertencia == '' )
		<div class="row" style="padding:5px; text-align: center;">
			<div class="col-md-6">

				<button class="btn btn-success" id="btn_previsualizar"> <i class="fa fa-eye"></i> Previsualizar </button>
			</div>
			<div class="col-md-6">
				<button class="btn btn-danger" id="btn_enviar"> <i class="fa fa-trash"></i> Enviar </button>
			</div>
		</div>
	@endif	
		
</div>



<div class="row" id="div_resultado_panel_generar">

</div>

@section('scripts2')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#btn_previsualizar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$(this).children('.fa-eye').attr('class','fa fa-spinner fa-spin');
		        //$(this).attr( 'disabled', 'disabled' );

		 		$("#div_cargando").show();
        		$("#div_resultado_panel_generar").html( '' );
				
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

			    	$("#btn_previsualizar").children('.fa-spinner').attr('class','fa fa-eye');
			        $("#btn_previsualizar").removeAttr( 'disabled' );

			        $('#div_cargando').hide();

        			$("#div_resultado_panel_generar").html( respuesta );
        			$("#div_resultado_panel_generar").fadeIn( 1000 );
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
        		$("#div_resultado_panel_generar").html( '' );

				var form = $('#formulario_inicial');
				var url = "{{ url('nom_retirar_retefuente') }}" + '/' + $('#nom_doc_encabezado_id').val();

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

        			$("#div_resultado_panel_generar").html( respuesta );
        			$("#div_resultado_panel_generar").fadeIn( 1000 );
			    });
		    });

		});
	</script>
@endsection